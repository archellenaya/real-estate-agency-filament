<?php

/**
 * Created by PhpStorm.
 * User: markbonnici
 * Date: 03/07/2015
 * Time: 10:01
 */

namespace App\Niu\Transformers;

use Illuminate\Support\Facades\Log;
use App\Components\Services\Impl\HTMLScraperService;


class EmailNotificationTransformer extends Transformer
{
	private $HTMLScraperService;
	public function __construct(
		HTMLScraperService $HTMLScraperService,
	) {
		$this->HTMLScraperService    = $HTMLScraperService;
	}
	public function transform($email_notification)
	{

		$html_scraped_data = $this->HTMLScraperService->scrape($email_notification->body);

		return [
			'id'             	=> $email_notification['id'],
			'property_ref'   	=> $html_scraped_data['property_ref'],
			'consultant_name'	=> $html_scraped_data['consultant_name'],
			'recipients'		=> $email_notification['recipient'],
			'is_sent' 			=> $email_notification['sent'],
			'date_sent' 		=> $email_notification['date_sent']
		];
	}
}
