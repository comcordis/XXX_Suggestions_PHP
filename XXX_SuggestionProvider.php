<?php

abstract class XXX_SuggestionProviderHelpers
{
	public static function processRawSuggestions ($valueAskingSuggestions = '', $rawSuggestions = array(), $maximum = 0, $doubles = false)
	{
		$maximum = XXX_Default::toPositiveInteger($maximum, 0);
		$processedSuggestions = array();
		
		if (!$doubles)
		{
			$rawSuggestions = array_unique($rawSuggestions);
			
			$rawSuggestions = XXX_Array::filterOutUndefined($rawSuggestions);
			$rawSuggestions = XXX_Array::filterOutNull($rawSuggestions);
		}
		
		$valueAskingSuggestionsLowerCase = XXX_String::convertToLowerCase($valueAskingSuggestions);
		
		for ($i = 0, $iEnd = XXX_Array::getFirstLevelItemTotal($rawSuggestions); $i < $iEnd; ++$i)
		{
			$rawSuggestion = $rawSuggestions[$i];
			$rawSuggestionLowerCase = XXX_String::convertToLowerCase($rawSuggestion);
			
			$processedSuggestion = array();
			$processedSuggestion['valueAskingSuggestions'] = $valueAskingSuggestions;
			$processedSuggestion['rawSuggestion'] = $rawSuggestion;
			$processedSuggestion['complement'] = '';
			
			if (XXX_String::findFirstPosition($rawSuggestionLowerCase, $valueAskingSuggestionsLowerCase) === 0)
			{
				$processedSuggestion['complement'] = XXX_String::getPart($rawSuggestion, XXX_String::getCharacterLength($valueAskingSuggestions));
			}
			
			$processedSuggestions[] = $processedSuggestion;
			
			if ($maximum > 0 && XXX_Array::getFirstLevelItemTotal($processedSuggestions) == $maximum)
			{
				break;
			}
		}
		
		return $processedSuggestions;
	}
	
	public static function composeResponse ($suggestions, $type = 'processed')
	{
		$result = false;
		
		$result = array
		(
			'type' => $type,
			'suggestions' => $suggestions
		);
		
		$result = XXX_String_JSON::encode($result);
		
		return $result;
	}
}
		
?>