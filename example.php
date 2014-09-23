<?php
require_once dirname(__file__) . '/scrapyd_api.php';
$api = new ScrapydAPI('http://zetng.com:6800/');
$api->client->extra=array(
	'proxy'=>'localhost:8888',
	);
//foreach($api->list_projects() as $project){
//    echo "<h2>{$project}</h2>";
//    var_dump($api->list_jobs($project));
//}
echo $api->schedule('p1', 'comment', array(), array(
	'asin'=>'http://www.amazon.com/Google-Nexus-Unlocked-Phone-Black/product-reviews/B00GD6H0NU/ref=cm_cr_dp_see_all_summary?ie=UTF8&showViewpoints=1&sortBy=byRankDescending',
	));

