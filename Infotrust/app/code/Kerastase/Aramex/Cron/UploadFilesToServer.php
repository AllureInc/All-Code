<?php
/**
 * Kerastase Package
 * User: wbraham
 * Date: 7/16/19
 * Time: 2:17 PM
 */

namespace Kerastase\Aramex\Cron;

use Exception;
use Magento\Framework\Exception\FileSystemException;

class UploadFilesToServer
{
    const SO_FOLDER = 'so';
    const ASN_FOLDER = 'asn';
    const PENDING_TO_SEND_FOLDER ='pool/';
    const SENT_OUTBOX ='sent/';
    const FAILED_OUTBOX ='failed/';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $sftp;

    protected $helper;
    protected $storeManager;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $directory;
    

    /**
     * FileUploader constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Kerastase\Aramex\Logger\Logger $logger,
        \Kerastase\Aramex\Helper\Data $helper,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->sftp = $sftp;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $this->logger->info('############################## START UPLOAD FILES TO SERVER CRON ####################################');
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website)
        {
            $storeId = $website->getDefaultStore()->getId();
            $this->storeManager->setCurrentStore($storeId);
            $this->logger->info('Store Info',  $this->storeManager->getStore()->toArray());
            
            if($this->helper->getIsEnabled() == 0)
            {
                $this->logger->info('### Aramex Integration disabled for this store');
            }
            else
            {        
                $host = $this->helper->getPath("aramex/general/host_expression"); $this->logger->info('HOST :'.  $host);
                $user = $this->helper->getPath("aramex/general/username"); $this->logger->info('USER :'.  $user);
                $password = $this->helper->getPath("aramex/general/password"); $this->logger->info('PWD :'.  $password);
                $port = $this->helper->getPath("aramex/general/port"); $this->logger->info('PORT :'.  $port);

                $soFolder = $this->helper->getToBeSentFolderPath(self::SO_FOLDER).'/'.self::PENDING_TO_SEND_FOLDER;
                $soLocalSuccess = $this->helper->getToBeSentFolderPath(self::SO_FOLDER).'/'.self::SENT_OUTBOX;
                $soLocalFailure = $this->helper->getToBeSentFolderPath(self::SO_FOLDER).'/'.self::FAILED_OUTBOX;
                $arraySO = $this->helper->getAllFiles($soFolder);
                $destinationFolderSO = $this->helper->getDestinationFolderSO();

                $this->logger->info("### SO FOLDERS ---> $soFolder - $soLocalSuccess - $soLocalFailure - $destinationFolderSO");

                $asnFolder = $this->helper->getToBeSentFolderPath(self::ASN_FOLDER).'/'.self::PENDING_TO_SEND_FOLDER;
                $asnLocalSuccess = $this->helper->getToBeSentFolderPath(self::ASN_FOLDER).'/'.self::SENT_OUTBOX;
                $asnLocalFailure = $this->helper->getToBeSentFolderPath(self::ASN_FOLDER).'/'.self::FAILED_OUTBOX;
                $arrayASN = $this->helper->getAllFiles($asnFolder);
                $destinationFolderASN = $this->helper->getDestinationFolderASN();

                $this->logger->info("### ASN FOLDERS ---> $asnFolder - $asnLocalSuccess - $asnLocalFailure - $destinationFolderASN");


                $config['hostname'] = $host;
                $config['username'] = $user;
                $config['password'] = $password;

                $this->logger->info('##SFTP HOST##  '.$host.' ###SFTP USERNAME###  '.$user.'## SFTP PASSWORD## '.$password.'##SFTP PORT##'.$port);


                try {

                    $connection = ssh2_connect($host, $port);
                    if(ssh2_auth_password($connection, $user, $password)) {
                        
                        $sftp = ssh2_sftp($connection);

                        $this->logger->info('## list of SO files to be sent ',$arraySO);
                        foreach ($arraySO as $item) {
                            $fileData = file_get_contents($soFolder.'/'.$item);
                            $fileStream = fopen( "ssh2.sftp://$sftp"."$destinationFolderSO/$item", "wb" );
                            fwrite($fileStream, $fileData);
                            fclose($fileStream);

                            rename($soFolder.$item, $soLocalSuccess. 'SO_'.$item);
                        }
                        
                        $this->logger->info('## list of ASN files to be sent ',$arrayASN);
                        foreach ($arrayASN as $item) {
                            $fileData = file_get_contents($asnFolder.'/'.$item);
                            $fileStream = fopen( "ssh2.sftp://$sftp"."$destinationFolderASN/$item", "wb" );
                            fwrite($fileStream, $fileData);
                            fclose($fileStream);

                            rename($asnFolder.$item, $asnLocalSuccess. 'ASN_'.$item);
                        }
                    }
                    unset($connection);

                } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                    $this->logger->info('We cannot send files to Aramex folder SO/ASN :'.$exception->getMessage());
                }


/*
                try {
                    $connexion = $this->createConnection($config);
                    $this->logger->info('## list of SO files to be sent ',$arraySO);
                    foreach ($arraySO as $item) {
                        $this->sendFileToAramex($connexion,$item, $destinationFolderSO, $soFolder);
                    }
                    $connexion->close();
                } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                    throw new  $exception(
                        __('We cannot send files to Aramex folder SO.')
                    );
                }

                try {
                    $connexion = $this->createConnection($config);
                    $this->logger->info('## list of ASN files to be sent ',$arrayASN);
                    foreach ($arrayASN as $item) {
                        $this->sendFileToAramex($connexion,$item, $destinationFolderASN, $asnFolder);
                    }
                    $connexion->close();
                } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                    throw new  $exception(
                        __('We cannot send files to Aramex folder SO.')
                    );
                }
*/
            }
        }

        $this->logger->info('############################## END UPLOAD FILES TO SERVER CRON ####################################');
    }
    /**
     * Connect to an SFTP server using specified configuration
     *
     * @param array $config
     * @return \Magento\Framework\Filesystem\Io\Sftp
     * @throws \InvalidArgumentException
     */
    public function createConnection(array $config)
    {
        if (!isset($config['hostname'])
            || !isset($config['username'])
            || !isset($config['password'])
        ) {
            throw new \InvalidArgumentException('Required config elements: hostname, username, password');
        }
        $connection = $this->sftp;
        $connection->open(
            ['host' => $config['hostname'], 'username' => $config['username'], 'password' => $config['password'],'timeout'=>86000]
        );
          return $connection;
    }

    /**
     * @param $file
     */
    public function sendFileToAramex($connexion,$file,$destinationFolder, $localFolder)
    {
        try {
            $connexion->cd($destinationFolder);
            if(is_writable($destinationFolder)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We cannot send the file target is not writable ')
                );
            }
            $this->logger->info('##sendFileToAramex ##### FULL FILE NAME ###  '.$localFolder.'/'.$file);

            $content = file_get_contents($localFolder.'/'.$file);
            $connexion->write($file, $content);

        } catch (Exception $e) {
            $this->logger->info($e);
        }
    }
}
