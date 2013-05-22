<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * @category  Shopware
 * @package   Shopware\Plugins\MarketingAggregate\Controllers\Backend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_TopSeller extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Helper function to get access on the TopSeller component.
     *
     * @return Shopware_Components_TopSeller
     */
    public function TopSeller()
    {
        return Shopware()->TopSeller();
    }

    /**
     * Helper function to initials the s_articles_top_seller table.
     * This table is used for the new shopware top seller function.
     */
    public function initTopSellerAction()
    {
        $offset = $this->Request()->get('offset', 0);
        $limit = $this->Request()->get('limit', 100);

        if ($offset == 0) {
            error_log("reset top seller" . "\n", 3, '/var/log/test.log');
            $sql = "DELETE FROM s_articles_top_seller_ro";
            Shopware()->Db()->query($sql);
        }

        $this->TopSeller()->initTopSeller($limit);

        $this->View()->assign(array('success' => true));
    }

    /**
     * Controller action which can be accessed over an request
     * This function is used to get the whole article count.
     */
    public function getTopSellerCountAction()
    {
        $sql = "SELECT COUNT(id) FROM s_articles";
        $count = Shopware()->Db()->fetchOne($sql);
        $this->View()->assign(array('success' => true, 'data' => array('count' => $count)));
    }
}


