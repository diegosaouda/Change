<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\User\Http\Rest\Actions;

use Change\Http\Rest\Result\ArrayResult;
use Zend\Http\Response as HttpResponse;

/**
 * @name \Rbs\User\Http\Rest\Actions\GetUserTokens
 */
class GetUserTokens
{

	/**
	 * @param \Change\Http\Event $event
	 */
	public function execute($event)
	{

		$userId = $event->getRequest()->getQuery('userId');

		$qb = $event->getApplicationServices()->getDbProvider()->getNewQueryBuilder();
		$fb = $qb->getFragmentBuilder();

		$qb->select($fb->column('token'), $fb->column('realm'), $fb->column('device'), $fb->column('application'),
			$fb->column('creation_date'), $fb->column('validity_date'))
			->from($fb->table($qb->getSqlMapping()->getOAuthTable()))
			->innerJoin($fb->table($qb->getSqlMapping()->getOAuthApplicationTable()), $fb->column('application_id'))
			->where($fb->logicAnd(
				$fb->eq($fb->column('accessor_id'), $fb->integerParameter('accessor_id')),
				$fb->eq($fb->column('token_type'), $fb->parameter('token_type')),
				$fb->gt($fb->column('validity_date'), $fb->dateTimeParameter('validity_date'))
			));
		$sq = $qb->query();

		$now = new \DateTime();
		$sq->bindParameter('accessor_id', $userId);
		$sq->bindParameter('token_type', \Change\Http\OAuth\OAuthDbEntry::TYPE_ACCESS);
		$sq->bindParameter('validity_date', $now);

		$rowAssoc = $sq->getResults($sq->getRowsConverter()
			->addStrCol('token', 'realm', 'device', 'application')->addDtCol('creation_date' ,'validity_date')
		);

		$array = array();

		foreach ($rowAssoc as $row)
		{
			$row['creation_date'] = $row['creation_date']->format(\DateTime::ISO8601);
			$row['validity_date'] = $row['validity_date']->format(\DateTime::ISO8601);
			$array[] = $row;
		}

		$result = new ArrayResult();
		$result->setArray($array);
		$result->setHttpStatusCode(HttpResponse::STATUS_CODE_200);

		$event->setResult($result);
	}
}