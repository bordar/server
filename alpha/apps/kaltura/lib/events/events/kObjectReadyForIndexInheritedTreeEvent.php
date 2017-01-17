<?php
/**
 * Applicative event that raised by the developer when indexed object is ready for indexing inherited tree in the index server
 */
class kObjectReadyForIndexInheritedTreeEvent extends kApplicativeEvent implements IBorhanMultiDeferredEvent
{
	const EVENT_CONSUMER = 'kObjectReadyForIndexInheritedTreeEventConsumer';

	private $partnerCriteriaParams;

	public function getConsumerInterface()
	{
		return self::EVENT_CONSUMER;
	}

	/* (non-PHPdoc)
	 * @see IBorhanMultiDeferredEvent::setPartnerCriteriaParams()
	 */
	public function setPartnerCriteriaParams(array $partnerCriteriaParams)
	{
		$this->partnerCriteriaParams = $partnerCriteriaParams;
	}

	/**
	 * @param kObjectAddedEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected function doConsume(BorhanEventConsumer $consumer)
	{
		if(!$consumer->shouldConsumeReadyForIndexInheritedTreeEvent($this->object))
			return true;

		$additionalLog = '';
		if(method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';

		BorhanLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		$result = $consumer->objectReadyForIndexInheritedTreeEvent($this->object, $this->partnerCriteriaParams, $this->raisedJob);
		BorhanLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . '] object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $result;
	}

}