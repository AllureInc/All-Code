<?php
namespace  Kerastase\Core\Plugin\Price;

use Magento\Framework\Locale\Format;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Directory\Model\CurrencyFactory;

class PriceFormat
{
    /**
     * Scope Resolver
     *
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * Locale Resolver
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * Currency Factory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * Constructor
     *
     * @param ScopeResolverInterface $scopeResolver   Scope Resolver
     * @param ResolverInterface      $localeResolver  Locale Resolver
     * @param CurrencyFactory        $currencyFactory Currency Resolver
     */
    public function __construct(
        ScopeResolverInterface $scopeResolver,
        ResolverInterface $localeResolver,
        CurrencyFactory $currencyFactory
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->localeResolver = $localeResolver;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Modify precision for JPY
     *
     * @param \Magento\Framework\Locale\Format $subject      Currency Format Obj
     * @param \Closure                         $proceed      Closure
     * @param null|string                      $localeCode   Locale Code
     * @param null|string                      $currencyCode Currency Code
     *
     * @return mixed
     */
    public function aroundGetPriceFormat(
        Format $subject,
        \Closure $proceed,
        $localeCode = null,
        $currencyCode = null
    ) {
        $result = $proceed($localeCode, $currencyCode);
        // Tweak for precisions
        $result['precision'] = '0';
        $result['requiredPrecision'] = '0';
        return $result;
    }
}
