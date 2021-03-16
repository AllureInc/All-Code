<?php
namespace Cor\Eventmanagement\Controller\Adminhtml\Event;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $helper = $this->_objectManager->create('Cor\Eventmanagement\Helper\Data');
        $data = $this->getRequest()->getParams();
        $savedIds = [];
        if (isset($data['event_start_date'])) {
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $data['event_start_date'])) {
                unset($data['event_start_date']);
            }
        }

        if (isset($data['event_end_date'])) {
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $data['event_end_date'])) {
                unset($data['event_end_date']);
            }
        }

        if ($data) {
            $model = $this->_objectManager->create('Cor\Eventmanagement\Model\Event');
            $modelEventArtist = $this->_objectManager->create('Cor\Eventmanagement\Model\Eventartist');

            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $model->load($id);
            }

            if (isset($data['tax_values'])) {
                $data['tax_values'] = json_encode($data['tax_values']);
            }

            $model->setData($data);
            try {
                $model->save();

                $event_id = $model->getId();

                if (isset($data['event_artist'])) {
                    $event_artists = $data['event_artist'];
                    $collection = $modelEventArtist->getCollection()->addFieldToFilter('event_id', array('eq' => $event_id));

                    /* code to add/update/delete the event artist association */
                    if (!empty($collection->getData())) {
                        foreach ($collection->getData() as $eventArtistDetail) {
                            $delete = true;
                            foreach ($event_artists as $event_artist) {
                                if ($event_artist['id'] == $eventArtistDetail['id']) {
                                    $artist_cut = json_encode($event_artist['artist_cut']);

                                    $modelEventArtist->load($event_artist['id']);
                                    $modelEventArtist->setArtistCut($artist_cut);
                                    $modelEventArtist->save();
                                    $modelEventArtist->unsetData();
                                    $delete = false;
                                }
                            }
                            if ($delete) {
                                $row = $this->_objectManager->get('Cor\Eventmanagement\Model\Eventartist')->load($eventArtistDetail['id']);
                                $row->delete();
                            }
                        }
                    }
                    foreach ($event_artists as $event_artist) {
                        if ($event_artist['id'] == 0) {
                            $artist_id = $event_artist['artist'];
                            $artist_cut = json_encode($event_artist['artist_cut']);

                            $eventArtist = ['event_id' => $event_id, 'artist_id' => $artist_id, 'artist_cut' => $artist_cut];
                            $modelEventArtist->setData($eventArtist);
                            $modelEventArtist->save();
                            $modelEventArtist->unsetData();
                        }
                    }
                } else {
                    $collection = $modelEventArtist->getCollection()->addFieldToFilter('event_id', array('eq' => $event_id));
                    if (!empty($collection->getData())) {
                        foreach ($collection->getData() as $eventArtistDetail) {
                            $row = $this->_objectManager->get('Cor\Eventmanagement\Model\Eventartist')->load($eventArtistDetail['id']);
                            $row->delete();
                        }
                    }
                }

                $event_status = $data['event_status'];

                if ($event_status == 1) {
                    $helper->sendMailOnEventClose('admin');
                }

                $this->messageManager->addSuccess(__('Event has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array(
                        'id' => $model->getId() ,
                        '_current' => true
                    ));
                    return;
                }

                $this->_redirect('*/*/');
                return;
            }

            catch(MagentoFrameworkModelException $e) {
                $this->messageManager->addError($e->getMessage());
            }

            catch(RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            }

            catch(Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the event.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', array(
                'id' => $this->getRequest()->getParam('id')
            ));
            return;
        }

        $this->_redirect('*/*/');
    }
}