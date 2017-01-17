<?php

class BorhanManifestException extends Exception
{
	
}

$config = array();
$client = null;
$serviceUrl = null;
$error=null;
/* @var $client BorhanMonitorClient */
require_once __DIR__  . '/common.php';

$options = getopt('', array(
	'service-url:',
	'debug',
	'entry-id:',
	'entry-reference-id:',
	'protocol:',
	'format:',
));

if(!isset($options['entry-id']) && !isset($options['entry-reference-id']))
{
	echo "One of arguments entry-id or entry-reference-id is required";
	exit(-1);
}

$start = microtime(true);
$monitorResult = new BorhanMonitorResult();
$apiCall = null;
try
{
	$apiCall = 'session.start';
	$ks = $client->session->start($config['monitor-partner']['adminSecret'], 'monitor-user', BorhanSessionType::ADMIN, $config['monitor-partner']['id']);
	$client->setKs($ks);
	
	$entry = null;
	/* @var $entry BorhanMediaEntry */
	if(isset($options['entry-id']))
	{
		$apiCall = 'media.get';
		$entry = $client->media->get($options['entry-id']);
	}
	elseif(isset($options['entry-reference-id']))
	{
		$apiCall = 'baseEntry.listByReferenceId';
		$baseEntryList = $client->baseEntry->listByReferenceId($options['entry-reference-id']);
		/* @var $baseEntryList BorhanBaseEntryListResponse */
		if(!count($baseEntryList->objects))
			throw new Exception("Entry with reference id [" . $options['entry-reference-id'] . "] not found");
			
		$entry = reset($baseEntryList->objects);
	}
	
	if($entry->status != BorhanEntryStatus::READY)
		throw new Exception("Entry id [$entry->id] is not ready for play");
	
	$params = array(
		'entryId' => $entry->id,
	);
	
	$format = null;
	if(isset($options['format']))
	{
		$params['format'] = $options['format'];
		$format = $options['format'];
	}
	
	if(isset($options['protocol']))
		$params['protocol'] = $options['protocol'];
			
	$params['a'] = 'a.f4m';
		
	$manifestLocalPath = tempnam(sys_get_temp_dir(), 'playManifest');
	$client->setDestinationPath($manifestLocalPath);
	$manifestUrl = "$clientConfig->serviceUrl/p/-4/sp/-400/playManifest";
	$headers = array();
	$httpCode = $client->extWidget($manifestUrl, $params, $headers, false);
	
	if($format == 'url')
	{
		if($httpCode != 302)
		{
			throw new BorhanManifestException("fetch manifest failed, HTTP Code: $httpCode, URL: $manifestUrl");
		}
		if(isset($headers['x-borhan-app']) && strpos($headers['x-borhan-app'], 'exiting on error') === 0)
		{
			list($prefix, $message) = explode(' - ', $headers['x-borhan-app'], 2);
			throw new BorhanManifestException($message);
		}
	}
	else
	{
		if($httpCode != 200)
		{
			throw new BorhanManifestException("fetch manifest failed, HTTP Code: $httpCode, URL: $manifestUrl");
		}
		if(isset($headers['x-borhan-app']) && strpos($headers['x-borhan-app'], 'exiting on error') === 0)
		{
			list($prefix, $message) = explode(' - ', $headers['x-borhan-app'], 2);
			throw new BorhanManifestException($message);
		}
		if(!file_exists($manifestLocalPath) || !filesize($manifestLocalPath))
		{
			throw new BorhanManifestException("no manifest file returned, URL: $manifestUrl");
		}
	}
	
	switch($format)
	{
		case 'rtmp':
			$manifest = new SimpleXMLElement($manifestLocalPath, LIBXML_NOERROR | LIBXML_NOWARNING, true);
			
			if($manifest->getName() != 'manifest')
				throw new BorhanManifestException("root element expected to be 'manifest', '" . $manifest->getName() . "' returned.");
			
			if(!isset($manifest->id))
				throw new BorhanManifestException("id element expected under manifest element.");
				
			if(strval($manifest->id) != $entry->id)
				throw new BorhanManifestException("id value should be the entry id, '$manifest->id' returned.");
			
			if(!isset($manifest->mimeType))
				throw new BorhanManifestException("mimeType element expected under manifest element.");
				
			if(strval($manifest->mimeType) != 'video/x-flv')
				throw new BorhanManifestException("mimeType value should be 'video/x-flv', '$manifest->mimeType' returned.");
			
			if(!isset($manifest->streamType))
				throw new BorhanManifestException("streamType element expected under manifest element.");
				
			if(strval($manifest->streamType) != 'recorded')
				throw new BorhanManifestException("streamType value should be 'recorded', '$manifest->streamType' returned.");
			
			if(!isset($manifest->duration))
				throw new BorhanManifestException("duration element expected under manifest element.");
				
//			$expectedDuration = $entry->msDuration / 1000;
//			if(floatval($manifest->duration) != $expectedDuration)
//				throw new BorhanManifestException("duration value should be $expectedDuration, $manifest->duration returned.");
			
			if(!isset($manifest->media))
				throw new BorhanManifestException("media element expected under manifest element.");
				
			foreach($manifest->media as $media)
			{
				$mediaAttributes = $media->attributes();
				
				if(!isset($mediaAttributes->bitrate))
					throw new BorhanManifestException("bitrate attribute expected in media element.");
					
				if(!isset($mediaAttributes->width))
					throw new BorhanManifestException("width attribute expected in media element.");
					
				if(!isset($mediaAttributes->height))
					throw new BorhanManifestException("height attribute expected in media element.");
					
				if(!isset($mediaAttributes->url))
					throw new BorhanManifestException("url attribute expected in media element.");
			}
			break;
				
		case 'sl':
			$manifest = new SimpleXMLElement($manifestLocalPath, LIBXML_NOERROR | LIBXML_NOWARNING, true);
			
			if($manifest->getName() != 'manifest')
				throw new BorhanManifestException("root element expected to be 'manifest', '" . $manifest->getName() . "' returned.");
			
			$manifestAttributes = $manifest->attributes();
				
			if(!isset($manifestAttributes->url))
				throw new BorhanManifestException("url attribute expected in manifest element.");
				
			if(!isset($manifest->id))
				throw new BorhanManifestException("id element expected under manifest element.");
				
			if(strval($manifest->id) != $entry->id)
				throw new BorhanManifestException("id value should be the entry id, '$manifest->id' returned.");
			
			if(!isset($manifest->streamType))
				throw new BorhanManifestException("streamType element expected under manifest element.");
				
			if(strval($manifest->streamType) != 'recorded')
				throw new BorhanManifestException("streamType value should be 'recorded', '$manifest->streamType' returned.");
			
			if(!isset($manifest->duration))
				throw new BorhanManifestException("duration element expected under manifest element.");
				
//			$expectedDuration = $entry->msDuration / 1000;
//			if(floatval($manifest->duration) != $expectedDuration)
//				throw new BorhanManifestException("duration value should be $expectedDuration, $manifest->duration returned.");
			
			$serveFlavorUrl = strval($manifestAttributes->url);
			
			$mediaLocalPath = tempnam(sys_get_temp_dir(), 'serveFlavor');
			$client->setDestinationPath($mediaLocalPath);
			$headers = array();
			$httpCode = $client->extWidget($serveFlavorUrl, $params, $headers, false);
	
			if($httpCode != 200)
			{
				throw new BorhanManifestException("serve flavor failed, HTTP Code: $httpCode, URL: $manifestUrl");
			}
			if(isset($headers['x-borhan-app']) && strpos($headers['x-borhan-app'], 'exiting on error') === 0)
			{
				list($prefix, $message) = explode(' - ', $headers['x-borhan-app'], 2);
				throw new BorhanManifestException($message);
			}
			if(!file_exists($mediaLocalPath) || !filesize($mediaLocalPath))
			{
				throw new BorhanManifestException("no media file returned, URL: $serveFlavorUrl");
			}
			
			break;
				
		case 'applehttp':
			$manifest = file_get_contents($manifestLocalPath);
			if(strpos($manifest, '#EXTM3U') !== 0)
				throw new BorhanManifestException("apple HTTP format must start with header '#EXTM3U'");
				
			$matches = null;
			if(!preg_match_all('/#EXT-X-STREAM-INF:PROGRAM-ID=\d+,BANDWIDTH=\d+\n([^\n]+)/', $manifest, $matches))
				throw new BorhanManifestException("manifest format does not match Apple HTTP expected format.");
					
			foreach($matches[1] as $serveFlavorUrl)
			{
				$mediaLocalPath = tempnam(sys_get_temp_dir(), 'serveFlavor');
				$client->setDestinationPath($mediaLocalPath);
				$headers = array();
				$httpCode = $client->extWidget($serveFlavorUrl, $params, $headers, false);
		
				if($httpCode != 200)
				{
					throw new BorhanManifestException("serve flavor failed, HTTP Code: $httpCode, URL: $manifestUrl");
				}
				if(isset($headers['x-borhan-app']) && strpos($headers['x-borhan-app'], 'exiting on error') === 0)
				{
					list($prefix, $message) = explode(' - ', $headers['x-borhan-app'], 2);
					throw new BorhanManifestException($message);
				}
				if(!file_exists($mediaLocalPath) || !filesize($mediaLocalPath))
				{
					throw new BorhanManifestException("no media file returned, URL: $serveFlavorUrl");
				}
			}
			break;
	
		case 'hds':
		case 'hdnetwork':
			$manifest = new SimpleXMLElement($manifestLocalPath, LIBXML_NOERROR | LIBXML_NOWARNING, true);
			
			if($manifest->getName() != 'manifest')
				throw new BorhanManifestException("root element expected to be 'manifest', '" . $manifest->getName() . "' returned.");
			
			if(!isset($manifest->id))
				throw new BorhanManifestException("id element expected under manifest element.");
				
			if(strval($manifest->id) != $entry->id)
				throw new BorhanManifestException("id value should be the entry id, '$manifest->id' returned.");
			
			if(!isset($manifest->mimeType))
				throw new BorhanManifestException("mimeType element expected under manifest element.");
				
			if(strval($manifest->mimeType) != 'video/x-flv')
				throw new BorhanManifestException("mimeType value should be 'video/x-flv', '$manifest->mimeType' returned.");
			
			if(!isset($manifest->streamType))
				throw new BorhanManifestException("streamType element expected under manifest element.");
				
			if(strval($manifest->streamType) != 'recorded')
				throw new BorhanManifestException("streamType value should be 'recorded', '$manifest->streamType' returned.");
			
			if(!isset($manifest->duration))
				throw new BorhanManifestException("duration element expected under manifest element.");
				
//			$expectedDuration = $entry->msDuration / 1000;
//			if(floatval($manifest->duration) != $expectedDuration)
//				throw new BorhanManifestException("duration value should be $expectedDuration, $manifest->duration returned.");
			
			if(!isset($manifest->media))
				throw new BorhanManifestException("media element expected under manifest element.");
				
			foreach($manifest->media as $media)
			{
				$mediaAttributes = $media->attributes();
				
				if($format == 'hds')
				{
					if(!isset($mediaAttributes->bitrate))
						throw new BorhanManifestException("bitrate attribute expected in media element.");
						
					if(!isset($mediaAttributes->width))
						throw new BorhanManifestException("width attribute expected in media element.");
						
					if(!isset($mediaAttributes->height))
						throw new BorhanManifestException("height attribute expected in media element.");
				}
					
				if(!isset($mediaAttributes->url))
					throw new BorhanManifestException("url attribute expected in media element.");
			
				$serveFlavorUrl = strval($mediaAttributes->url);

				$mediaLocalPath = tempnam(sys_get_temp_dir(), 'serveFlavor');
				$client->setDestinationPath($mediaLocalPath);
				$headers = array();
				$httpCode = $client->extWidget($serveFlavorUrl, $params, $headers, false);

				if($httpCode != 200)
				{
					throw new BorhanManifestException("serve flavor failed, HTTP Code: $httpCode, URL: $manifestUrl");
				}
				if(isset($headers['x-borhan-app']) && strpos($headers['x-borhan-app'], 'exiting on error') === 0)
				{
					list($prefix, $message) = explode(' - ', $headers['x-borhan-app'], 2);
					throw new BorhanManifestException($message);
				}
				if(!file_exists($mediaLocalPath) || !filesize($mediaLocalPath))
				{
					throw new BorhanManifestException("no media file returned, URL: $serveFlavorUrl");
				}
			}
			break;
				
		case 'url':
			$mediaLocalPath = tempnam(sys_get_temp_dir(), 'serveFlavor');
			$client->setDestinationPath($mediaLocalPath);
			$headers = array();
			$httpCode = $client->extWidget($manifestUrl, $params, $headers, true);
			
			if($httpCode != 200)
			{
				throw new BorhanManifestException("fetch redirected flavor failed, HTTP Code: $httpCode, URL: $manifestUrl");
			}
			if(isset($headers['x-borhan-app']) && strpos($headers['x-borhan-app'], 'exiting on error') === 0)
			{
				list($prefix, $message) = explode(' - ', $headers['x-borhan-app'], 2);
				throw new BorhanManifestException($message);
			}
			if(!file_exists($manifestLocalPath) || !filesize($manifestLocalPath))
			{
				throw new BorhanManifestException("no redirected flavor file returned, URL: $manifestUrl");
			}
			break;
				
		case 'rtsp':
			$html = file_get_contents($manifestLocalPath);
			$matches = null;
			if(!preg_match('/<html><head><meta http-equiv="refresh" content="0;url=([^"]+)"><\/head><\/html>/', $html, $matches))
				throw new BorhanManifestException("HTML format does not match RTSP expected format.");
					
			$serveFlavorUrl = $clientConfig->serviceUrl . $matches[1];

			$mediaLocalPath = tempnam(sys_get_temp_dir(), 'serveFlavor');
			$client->setDestinationPath($mediaLocalPath);
			$headers = array();
			$httpCode = $client->extWidget($serveFlavorUrl, $params, $headers, false);

			if($httpCode != 200)
			{
				throw new BorhanManifestException("serve flavor failed, HTTP Code: $httpCode, URL: $manifestUrl");
			}
			if(isset($headers['x-borhan-app']) && strpos($headers['x-borhan-app'], 'exiting on error') === 0)
			{
				list($prefix, $message) = explode(' - ', $headers['x-borhan-app'], 2);
				throw new BorhanManifestException($message);
			}
			if(!file_exists($mediaLocalPath) || !filesize($mediaLocalPath))
			{
				throw new BorhanManifestException("no media file returned, URL: $serveFlavorUrl");
			}
			break;
				
		case 'hdnetworksmil':
			$manifest = new SimpleXMLElement($manifestLocalPath, LIBXML_NOERROR | LIBXML_NOWARNING, true);
			
			if($manifest->getName() != 'smil')
				throw new BorhanManifestException("root element expected to be 'smil', '" . $manifest->getName() . "' returned.");
			
			if(!isset($manifest->head))
				throw new BorhanManifestException("head element expected under smil element.");
				
			$metaData = array();
			foreach($manifest->head->children() as $meta)
			{
				/* @var $meta SimpleXMLElement */
				if($meta->getName() != 'meta')
					throw new BorhanManifestException("only meta elements expected under smil/head element, '" . $meta->getName() . "' returned.");
				
				$metaAttributes = $meta->attributes();
				
				if(!isset($metaAttributes->name))
					throw new BorhanManifestException("name attribute expected in smil/head/meta elements.");
				
				if(!isset($metaAttributes->content))
					throw new BorhanManifestException("content attribute expected in smil/head/meta elements.");
					
				$metaData[strval($metaAttributes->name)] = strval($metaAttributes->content);
			}
			
			if(!isset($metaData['vod']))
				throw new BorhanManifestException("meta element with name 'vod' expected under smil/head element.");
			
			if(strtolower($metaData['vod']) != 'true')
				throw new BorhanManifestException("vod meta element expected to be true.");
				
			if(!isset($metaData['httpBase']))
				throw new BorhanManifestException("meta element with name 'httpBase' expected under smil/head element.");
				
			$httpBase = $metaData['httpBase'];
			
			if(!isset($manifest->body))
				throw new BorhanManifestException("body element expected under smil element.");
	
			if(!isset($manifest->body->switch))
				throw new BorhanManifestException("switch element expected under smil/body element.");
	
			foreach($manifest->body->switch->children() as $video)
			{
				/* @var $video SimpleXMLElement */
				if($video->getName() != 'video')
					throw new BorhanManifestException("only video elements expected under smil/body/switch element, '" . $video->getName() . "' returned.");
				
				$videoAttributes = $video->attributes();
				
				if(!isset($videoAttributes->src))
					throw new BorhanManifestException("src attribute expected in smil/body/switch/video elements.");
				
				$systemBitrate = 'system-bitrate';
				if(!isset($videoAttributes->$systemBitrate))
					throw new BorhanManifestException("$systemBitrate attribute expected in smil/body/switch/video elements.");
				
				$serveFlavorUrl = $httpBase . strval($videoAttributes->src);

				$mediaLocalPath = tempnam(sys_get_temp_dir(), 'serveFlavor');
				$client->setDestinationPath($mediaLocalPath);
				$headers = array();
				$httpCode = $client->extWidget($serveFlavorUrl, $params, $headers, false);

				if($httpCode != 200)
				{
					throw new BorhanManifestException("serve flavor failed, HTTP Code: $httpCode, URL: $manifestUrl");
				}
				if(isset($headers['x-borhan-app']) && strpos($headers['x-borhan-app'], 'exiting on error') === 0)
				{
					list($prefix, $message) = explode(' - ', $headers['x-borhan-app'], 2);
					throw new BorhanManifestException($message);
				}
				if(!file_exists($mediaLocalPath) || !filesize($mediaLocalPath))
				{
					throw new BorhanManifestException("no media file returned, URL: $serveFlavorUrl");
				}
			}
			
			break;
				
		case 'hdnetworkmanifest':
		case 'http':
		default:
			$manifest = new SimpleXMLElement($manifestLocalPath, LIBXML_NOERROR | LIBXML_NOWARNING, true);
			
			if($manifest->getName() != 'manifest')
				throw new BorhanManifestException("root element expected to be 'manifest', '" . $manifest->getName() . "' returned.");
			
			if(!isset($manifest->id))
				throw new BorhanManifestException("id element expected under manifest element.");
				
			if(strval($manifest->id) != $entry->id)
				throw new BorhanManifestException("id value should be the entry id, '$manifest->id' returned.");
			
			if(!isset($manifest->mimeType))
				throw new BorhanManifestException("mimeType element expected under manifest element.");
				
			if(strval($manifest->mimeType) != 'video/x-flv')
				throw new BorhanManifestException("mimeType value should be 'video/x-flv', '$manifest->mimeType' returned.");
			
			if(!isset($manifest->streamType))
				throw new BorhanManifestException("streamType element expected under manifest element.");
				
			if(strval($manifest->streamType) != 'recorded')
				throw new BorhanManifestException("streamType value should be 'recorded', '$manifest->streamType' returned.");
			
			if(!isset($manifest->duration))
				throw new BorhanManifestException("duration element expected under manifest element.");
				
//			$expectedDuration = $entry->msDuration / 1000;
//			if(floatval($manifest->duration) != $expectedDuration)
//				throw new BorhanManifestException("duration value should be $expectedDuration, $manifest->duration returned.");
			
			if(!isset($manifest->media))
				throw new BorhanManifestException("media element expected under manifest element.");
				
			$mediaAttributes = $manifest->media->attributes();
			
			if(!isset($mediaAttributes->bitrate))
				throw new BorhanManifestException("bitrate attribute expected in media element.");
				
			if(!isset($mediaAttributes->width))
				throw new BorhanManifestException("width attribute expected in media element.");
				
			if(!isset($mediaAttributes->height))
				throw new BorhanManifestException("height attribute expected in media element.");
				
			if(!isset($mediaAttributes->url))
				throw new BorhanManifestException("url attribute expected in media element.");
				
			$serveFlavorUrl = strval($mediaAttributes->url);
			
			$mediaLocalPath = tempnam(sys_get_temp_dir(), 'serveFlavor');
			$client->setDestinationPath($mediaLocalPath);
			$headers = array();
			$httpCode = $client->extWidget($serveFlavorUrl, $params, $headers, false);
	
			if($httpCode != 200)
			{
				throw new BorhanManifestException("serve flavor failed, HTTP Code: $httpCode, URL: $manifestUrl");
			}
			if(isset($headers['x-borhan-app']) && strpos($headers['x-borhan-app'], 'exiting on error') === 0)
			{
				list($prefix, $message) = explode(' - ', $headers['x-borhan-app'], 2);
				throw new BorhanManifestException($message);
			}
			if(!file_exists($mediaLocalPath) || !filesize($mediaLocalPath))
			{
				throw new BorhanManifestException("no media file returned, URL: $serveFlavorUrl");
			}
			
			break;
	}
	
	$monitorResult->executionTime = microtime(true) - $start;
	$monitorResult->value = $monitorResult->executionTime;
	$monitorResult->description = "Play manifest time: $monitorResult->value seconds";
}
catch(BorhanException $e)
{
	$monitorResult->executionTime = microtime(true) - $start;
	
	$error = new BorhanMonitorError();
	$error->code = $e->getCode();
	$error->description = $e->getMessage();
	$error->level = BorhanMonitorError::ERR;
	
	$monitorResult->errors[] = $error;
	$monitorResult->description = "Exception: " . get_class($e) . ", API: $apiCall, Code: " . $e->getCode() . ", Message: " . $e->getMessage();
}
catch(BorhanClientException $ce)
{
	$monitorResult->executionTime = microtime(true) - $start;
	
	$error = new BorhanMonitorError();
	$error->code = $ce->getCode();
	$error->description = $ce->getMessage();
	$error->level = BorhanMonitorError::CRIT;
	
	$monitorResult->errors[] = $error;
	$monitorResult->description = "Exception: " . get_class($ce) . ", API: $apiCall, Code: " . $ce->getCode() . ", Message: " . $ce->getMessage();
}
catch(BorhanManifestException $me)
{
	$monitorResult->executionTime = microtime(true) - $start;
	
	$error = new BorhanMonitorError();
	$error->code = $me->getCode();
	$error->description = $me->getMessage();
	$error->level = BorhanMonitorError::CRIT;
	
	$monitorResult->errors[] = $error;
	$monitorResult->description = $me->getMessage();
}
catch(Exception $ex)
{
	$monitorResult->executionTime = microtime(true) - $start;
	
	$error = new BorhanMonitorError();
	$error->code = $ex->getCode();
	$error->description = $ex->getMessage();
	$error->level = BorhanMonitorError::ERR;
	
	$monitorResult->errors[] = $error;
	$monitorResult->description = $ex->getMessage();
}

unlink($manifestLocalPath);
unlink($mediaLocalPath);
echo "$monitorResult";
if (isset($error)){
    exit(1);
}
