<?php

namespace Topxia\Service\Order\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Order\Dao\OrderRefundDao;

class OrderRefundDaoImpl extends BaseDao implements OrderRefundDao
{
    protected $table = 'order_refund';

    public function getRefund($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findRefundCountByUserId($userId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE userId = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId));
    }

    public function findRefundsByUserId($userId, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($userId)) ? : array();
    }

    public function searchRefunds($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }


    public function searchRefundCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addRefund($refund)
    {
        $affected = $this->getConnection()->insert($this->table, $refund);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert order refund error.');
        }
        return $this->getRefund($this->getConnection()->lastInsertId());
    }

    public function updateRefund($id, $refund)
    {
        $this->getConnection()->update($this->table, $refund, array('id' => $id));
        return $this->getRefund($id);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
                    ->from($this->table, $this->table)
                    ->andWhere('status = :status')
                    ->andWhere('userId = :userId')
                    ->andWhere('targetType = :targetType')
                    ->andWhere('orderId = :orderId')
                    ->andWhere('targetType = :targetType')
                    ->andWhere('userId IN ( :userIds )')
                    ->andWhere('targetId = :targetId')
                    ->andWhere('status <> :statusNotEqual')
                    ->andWhere('targetId IN ( :targetIds )');
                    

        if (isset($conditions['targetIds'])) {
            $builder->andWhere("targetId IN ( :targetIds )");
        }

        return $builder;           
    }

    public function findRefundsByIds(array $ids)
    {
        if(empty($ids)) {
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return  $this->getConnection()->fetchAll($sql, $ids);
    }

}