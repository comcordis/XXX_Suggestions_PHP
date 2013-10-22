<?php

abstract class XXX_SuggestionProvider
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
			
			$processedSuggestion['label'] = $valueAskingSuggestions . $processedSuggestion['complement'];
			$processedSuggestion['suggestedValue'] = $valueAskingSuggestions . $processedSuggestion['complement'];
			$processedSuggestion['data'] = array();
			
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


/*

2 modes:
	- max x results
	- filter results, long list (dropdown like yat now)

Default if empty, but focused, query (e.g. Italy for only italian results)

*/
/*
var XXX_SuggestionProvider = function ()
{
	this.ID = XXX.createID();
	
	this.processedSuggestions = [];
	
	this.allowCachedSuggestions = false;
	this.cachedSuggestions = [];
		
	this.valueAskingSuggestions = '';
	
	this.triedValuesToComplete = [];
	
	this.suggestionSource = 'fixed';
	this.fixedDataType = '';
	this.fixedSuggestions =
	{
		type: 'raw',
		suggestions: []
	};
	this.serverSideRoute = '';
	this.requestSuggestionsCallback = false;
	this.cancelRequestSuggestionsCallback = false;
	
	this.maximumResults = 0;
	
	this.composeSuggestionOptionLabelCallback = false;
	
	this.elements = {};
};

XXX_SuggestionProvider.prototype.setMaximumResults = function (maximumResults)
{
	this.maximumResults = XXX_Default.toPositiveInteger(maximumResults, 5);
};

XXX_SuggestionProvider.prototype.setComposeSuggestionOptionLabelCallback = function (composeSuggestionOptionLabelCallback)
{
	this.composeSuggestionOptionLabelCallback = composeSuggestionOptionLabelCallback;
};

XXX_SuggestionProvider.prototype.enableCachedSuggestions = function ()
{
	this.allowCachedSuggestions = true;
};

XXX_SuggestionProvider.prototype.disableCachedSuggestions = function ()
{
	this.allowCachedSuggestions = false;
	this.cachedSuggestions = [];
};

XXX_SuggestionProvider.prototype.setSuggestionSourceToServerSideRoute = function (route)
{
	this.suggestionSource = 'serverSideRoute';
	this.serverSideRoute = route;
};

XXX_SuggestionProvider.prototype.setSuggestionSourceToCallback = function (requestSuggestionsCallback, cancelRequestSuggestionsCallback)
{
	this.suggestionSource = 'callback';
	this.requestSuggestionsCallback = requestSuggestionsCallback;
	this.cancelRequestSuggestionsCallback = cancelRequestSuggestionsCallback;
};

XXX_SuggestionProvider.prototype.setSuggestionSourceToSimpleIndex = function (simpleIndex)
{
	this.suggestionSource = 'simpleIndex';
	this.simpleIndex = simpleIndex;
};

XXX_SuggestionProvider.prototype.setSuggestionSourceToFixed = function (fixedSuggestionsDataType, fixedSuggestions)
{
	this.fixedSuggestionsDataType = fixedSuggestionsDataType;
	this.suggestionSource = 'fixed';
	
	if (!(fixedSuggestions.type == 'raw' || fixedSuggestions.type == 'processed'))
	{
		fixedSuggestions =
		{
			type: 'raw',
			suggestions: fixedSuggestions
		};
	}
	
	this.fixedSuggestions = fixedSuggestions;
};

XXX_SuggestionProvider.prototype.setSuggestionSourceToGeocoder = function ()
{
	var temp = new XXX_GoogleMapsAPI_GeocoderSuggestionSource();
	
	this.setSuggestionSourceToCallback(temp.getRequestSuggestionsCallback());
	this.setComposeSuggestionOptionLabelCallback(temp.composeSuggestionOptionLabel);
};

XXX_SuggestionProvider.prototype.setSuggestionSourceToPlaces = function ()
{
	var temp = new XXX_GoogleMapsAPI_PlacesSuggestionSource();
	
	this.setSuggestionSourceToCallback(temp.getRequestSuggestionsCallback());
	this.setComposeSuggestionOptionLabelCallback(temp.composeSuggestionOptionLabel);
};

XXX_SuggestionProvider.prototype.cancelRequestSuggestions = function ()
{
	switch (this.suggestionSource)
	{
		case 'serverSideRoute':
			XXX_HTTP_Browser_Request_Asynchronous.cancelRequest(this.ID + '_requestSuggestions');
			break;
		case 'callback':
			if (this.cancelRequestSuggestionsCallback)
			{
				this.cancelRequestSuggestionsCallback();
			}
			break;
	}
};


XXX_SuggestionProvider.prototype.requestSuggestions = function (valueAskingSuggestions, completedCallback, failedCallback)
{
	this.cancelRequestSuggestions();
	
	this.valueAskingSuggestions = valueAskingSuggestions;
	this.completedCallback = completedCallback;
	this.failedCallback = failedCallback;
	
	this.processedSuggestions = [];
	
	var retrievalMethod = 'live';
		
	var valueAskingSuggestionsLowerCase = XXX_String.convertToLowerCase(valueAskingSuggestions);
	
	var cachedSuggestions = [];
	
	// try cache
	if (this.allowCachedSuggestions)
	{		
		for (var i = 0, iEnd = XXX_Array.getFirstLevelItemTotal(this.cachedSuggestions); i < iEnd; ++i)
		{
			var cachedSuggestion = this.cachedSuggestions[i];
			
			var suggestedValue = cachedSuggestion.suggestedValue;
			
			var matchedSuggestion = XXX_SuggestionProviderHelpers.tryMatchingSuggestion(valueAskingSuggestions, suggestedValue);
			
			if (matchedSuggestion)
			{
				cachedSuggestion.valueAskingSuggestions = valueAskingSuggestions;
				cachedSuggestion.matchType = matchedSuggestion.matchType;
				cachedSuggestion.suggestedValue = matchedSuggestion.suggestedValue;
				cachedSuggestion.complement = matchedSuggestion.complement;
				cachedSuggestion.label = matchedSuggestion.label;
				
				if (this.composeSuggestionOptionLabelCallback)
				{
					cachedSuggestion.label = this.composeSuggestionOptionLabelCallback(cachedSuggestion);
				}
				else
				{
					cachedSuggestion.label = XXX_SuggestionProviderHelpers.composeSuggestionOptionLabel(cachedSuggestion);
				}
				
				cachedSuggestions.push(cachedSuggestion);
			}
		}
		
		if (this.maximumResults > 0 && XXX_Array.getFirstLevelItemTotal(cachedSuggestions) >= this.maximumResults)
		{
			retrievalMethod = 'cache';
		}
	}
	
	if (retrievalMethod == 'live')
	{
		if (this.allowCachedSuggestions && XXX_Array.hasValue(this.triedValuesToComplete, XXX_String.convertToLowerCase(this.valueAskingSuggestions)))
		{
			retrievalMethod = 'cache';
		}
	}
	
	switch (retrievalMethod)
	{
		case 'cache':
			var limitedSuggestions = false;
			
			if (this.maximumResults > 0)
			{
				limitedSuggestions = XXX_Array.getPart(cachedSuggestions, 0, this.maximumResults);
			}
			else
			{
				limitedSuggestions = cachedSuggestions;
			}
			
			if (this.completedCallback)
			{
				this.completedCallback(this.valueAskingSuggestions, limitedSuggestions);
			}
			break;
		case 'live':
			var XXX_SuggestionProvider_instance = this;
			
			var completedCallback = function (suggestionsResponse)
			{
				XXX_SuggestionProvider_instance.completedResponseHandler(suggestionsResponse);
			};
			
			var failedCallback = function ()
			{
				XXX_SuggestionProvider_instance.failedResponseHandler();
			};
			
			switch (this.suggestionSource)
			{
				case 'serverSideRoute':
					XXX_HTTP_Browser_Request_Asynchronous.queueRequest(this.ID + '_requestSuggestions', XXX_URI.composeRouteURI(this.serverSideRoute), [{key: 'valueAskingSuggestions', value: valueAskingSuggestionsLowerCase}, {key: 'maximum', value: this.maximumResults}], completedCallback, 'json', false, 'body', false, failedCallback);
					break;
				case 'callback':
					this.requestSuggestionsCallback(valueAskingSuggestions, completedCallback, failedCallback);
					break;
				case 'simpleIndex':
					this.simpleIndex.executeQuery(valueAskingSuggestions);
					this.completedResponseHandler(this.simpleIndex.getSuggestionProviderSourceResponse())
					break;
				case 'fixed':
					this.completedResponseHandler(this.fixedSuggestions);
					break;
			}
			
			if (this.allowCachedSuggestions)
			{
				this.triedValuesToComplete.push(XXX_String.convertToLowerCase(this.valueAskingSuggestions));
			}
			break;
	}
};

XXX_SuggestionProvider.prototype.failedResponseHandler = function ()
{
	if (this.failedCallback)
	{
		this.failedCallback(this.valueAskingSuggestions);
	}
};

XXX_SuggestionProvider.prototype.completedResponseHandler = function (suggestionsResponse)
{
	if (suggestionsResponse && suggestionsResponse.type)
	{
		switch (suggestionsResponse.type)
		{
			case 'processed':
				this.processedSuggestions = suggestionsResponse.suggestions;
				
				for (var i = 0, iEnd = XXX_Array.getFirstLevelItemTotal(this.processedSuggestions); i < iEnd; ++i)
				{
					// Fix JSON bug, empty string being false
					if (this.processedSuggestions[i].complement === false)
					{
						this.processedSuggestions[i].complement = '';
					}
				}
				break;
			case 'raw':
				//this.processedSuggestions = XXX_SuggestionProviderHelpers.processRawSuggestions(this.valueAskingSuggestions, suggestionsResponse.suggestions, this.maximumResults, this.fixedSuggestionsDataType);		
				break;
		}
		
		// Label
		
		for (var i = 0, iEnd = XXX_Array.getFirstLevelItemTotal(this.processedSuggestions); i < iEnd; ++i)
		{
			if (this.composeSuggestionOptionLabelCallback)
			{
				this.processedSuggestions[i].label = this.composeSuggestionOptionLabelCallback(this.processedSuggestions[i]);
			}
			else
			{
				this.processedSuggestions[i].label = XXX_SuggestionProviderHelpers.composeSuggestionOptionLabel(this.processedSuggestions[i]);
			}
		}
		
		
		
		// Append new values to cache
		if (this.allowCachedSuggestions)
		{
			for (var i = 0, iEnd = XXX_Array.getFirstLevelItemTotal(this.processedSuggestions); i < iEnd; ++i)
			{
				var alreadyCached = false;
				
				for (var j = 0, jEnd = XXX_Array.getFirstLevelItemTotal(this.cachedSuggestions); j < jEnd; ++j)
				{
					if (this.processedSuggestions[i].suggestedValue == this.cachedSuggestions[j].suggestedValue)
					{
						alreadyCached = true;
					}
				}
				
				if (!alreadyCached)
				{
					this.cachedSuggestions.push(this.processedSuggestions[i]);
				}
			}
		}
		
		var limitedSuggestions = false;
		
		if (this.maximumResults > 0)
		{
			limitedSuggestions = XXX_Array.getPart(this.processedSuggestions, 0, this.maximumResults);
		}
		else
		{
			limitedSuggestions = this.processedSuggestions;
		}
				
		if (this.completedCallback)
		{
			this.completedCallback(this.valueAskingSuggestions, limitedSuggestions);
		}
	}
};

*/
		
?>