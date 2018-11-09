<?php
/**
 * Created by PhpStorm.
 * User: norbertas
 * Date: 18.11.8
 * Time: 14.30
 */

namespace Trollweb\JoynApi\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Trollweb\JoynApi\Helper\Data;

class Config extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    public function __construct(Context $context, Data $helper)
    {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }
}