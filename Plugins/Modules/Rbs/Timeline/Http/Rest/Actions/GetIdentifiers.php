<?php
namespace Rbs\Timeline\Http\Rest\Actions;

use Change\Http\Rest\Result\ArrayResult;
use Change\User\ProfileManager;
use Zend\Http\Response as HttpResponse;
/**
 * @name \Rbs\Timeline\Http\Rest\Actions\GetIdentifiers
 */
class GetIdentifiers
{

	/**
	 * @param \Change\Http\Event $event
	 */
	public function execute($event)
	{
		$autocomplete = $event->getRequest()->getQuery('autocomplete');
		$result = new ArrayResult();
		if (substr($autocomplete, 0, 1) === '@')
		{
			$identifier = '';
			if (substr($autocomplete, 0, 2) === '@+')
			{
				$model = 'Rbs_User_Group';
				$identifier = substr($autocomplete, 2);
			}
			else
			{
				$model = 'Rbs_User_User';
				$identifier = substr($autocomplete, 1);
			}
			$dqb = new \Change\Documents\Query\Query($event->getDocumentServices(), $model);
			$usersOrGroups = $dqb->getDocuments();
			$identifiersFiltered = [];
			foreach ($usersOrGroups as $userOrGroup)
			{
				/* @var $userOrGroup \Rbs\User\Documents\User */
				if (\Change\Stdlib\String::beginsWith($userOrGroup->getIdentifier(), $identifier))
				{
					//TODO hardcoded value for default avatar url
					$avatar = 'Rbs/Admin/img/user-default.png';
					$pm = new ProfileManager();
					$pm->setDocumentServices($event->getDocumentServices());
					$profile = $pm->loadProfile($userOrGroup, 'Rbs_Admin');
					if ($profile && $profile->getPropertyValue('avatar'))
					{
						$avatar = $profile->getPropertyValue('avatar');
					}
					$identifiersFiltered[] = [
						'identifier' => $userOrGroup->getIdentifier(),
						'avatar' => $avatar,
						'name' => $userOrGroup->getLabel()
					];
				}
			}
			$result->setArray($identifiersFiltered);
			$result->setHttpStatusCode(HttpResponse::STATUS_CODE_200);
			$event->setResult($result);
			return $result;
		}
		else
		{
			$result->setHttpStatusCode(HttpResponse::STATUS_CODE_500);
			return $result;
		}
	}
}