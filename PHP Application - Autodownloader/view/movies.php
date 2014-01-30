<ul class="main-list">
	<? while ($movie = $this->getShowMovieEpisode()): ?>
		<? $details = $movie->details; ?>
		<li data-data_id="<?=$details['imdb_id']?>" class="container">		
			<div class="image">
				<img height="203px" width="138px" src="<?=$movie->get_thumbnail()?>" />
				<? if ($movie->hasBeenWatched()): ?>
					<img src="/view/images/banner_watched_2.png" class="banner_watched">
				<? endif; ?>
			</div>
			<div class="title"><span><?=$details['title']?></span></div>
			<div class="date">
				<div class="release_date">
					<?=date("F j, Y",$details['released'])?> <?=!empty($details['certification']) ? '('.$details['certification'].')' : ''?>
				</div>
			</div>
			<div class="details">
				<?=$details['overview']?>
			</div>				
			<div class="download">
				<a href="#" class="button download" data-status="<?=$movie->download_status['column']?>">
					<span><?=$movie->download_status['text']?></span>
				</a>
				<a href="<?=$details['trailer']?>" class="button view-trailer">
					<span>View Trailer</span>
				</a>
			</div>
			<i class="rt_critics-score" data-url="<?=$movie->getRottenTomatoesScoreURL()?>"></i> 
		</li>
	<? endwhile; ?>
</ul>