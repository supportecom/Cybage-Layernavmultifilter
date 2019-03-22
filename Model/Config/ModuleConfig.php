<?php
/**
 * Cybage Layernavmultifilter Layered Navigation Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to access it on the World Wide Web, please send an email
 * To: Support_ecom@cybage.com.  We will send you a copy of the source file.
 *
 * @category  Layernavmultifilter Layered Navigation Plugin
 * @package   Cybage_Layernavmultifilter
 * @author    Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 * @copyright Copyright (c) 2019 Cybage Software Pvt. Ltd., India
 *            http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */

namespace Cybage\Layernavmultifilter\Model\Config;

class ModuleConfig extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var Magento\Framework\Module\Status
     */
    private $_comandLine;

    /**
     *
     */
    private $_cacheTypeList;

    /**
     *
     */
    private $_cacheFrontendPool;

    /**
     * @var string
     */
    protected $_runModelPath = '';
    const ENABLE_OPTION_PATH = 'multifilter/general/active';
    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Module\Status $comandLine
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param type $runModelPath
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\Model\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\App\Config\ScopeConfigInterface $config,
    \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
    \Magento\Framework\App\Config\ValueFactory $configValueFactory,
    \Magento\Framework\Module\Status $comandLine,
    \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
    \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
    \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
    $runModelPath = '', array $data = []
    )
    {
        $this->_runModelPath       = $runModelPath;
        $this->_configValueFactory = $configValueFactory;
        $this->_comandLine         = $comandLine;
        $this->_cacheTypeList      = $cacheTypeList;
        $this->_cacheFrontendPool  = $cacheFrontendPool;
        parent::__construct($context, $registry, $config, $cacheTypeList,
            $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $checkModuleStatus = $this->getData('groups/general/fields/active/value');
        $modules           = array('Cybage_Layernavmultifilter');
        try {
            if ($checkModuleStatus == '1') {
                /* Code to enable a module [ php bin/magento module:enable VENDORNAME_MODULENAME ] */
                $moduleStatus = $this->_comandLine->setIsEnabled(true, $modules);
            } else {
                /* Code to enable a module [ php bin/magento module:enable VENDORNAME_MODULENAME ] */
                $moduleStatus = $this->_comandLine->setIsEnabled(false, $modules);
                $this->_configValueFactory->create()->load(
                self::ENABLE_OPTION_PATH,
                'path'
                )->setValue(
                    1
                )->setPath(
                    self::ENABLE_OPTION_PATH
                )->save();
            }

            /* Code to clean cache [ php bin/magento:cache:clean ] */
            try {
                $types = array('config', 'layout', 'block_html', 'collections', 'reflection',
                    'db_ddl', 'eav', 'config_integration', 'config_integration_api',
                    'full_page', 'translate', 'config_webservice');
                foreach ($types as $type) {
                    $this->_cacheTypeList->cleanType($type);
                }
                foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                    $cacheFrontend->getBackend()->clean();
                }
            } catch (Exception $e) {
                echo $msg = 'Error during cache clean: '.$e->getMessage();
                die();
            }
        } catch (Exception $e) {
            echo $msg = 'Error during module enabling : '.$e->getMessage();
            die();
        }

        return parent::afterSave();
    }
}