<ul class="main-list">
	<? while ($tv = $this->getShowMovieEpisode()): ?>
		<? $details = $tv->details; ?>
		<? 
			// We do this here so we can see if the show is watched up to date (using isCaughtUp)
			$tv->setSeasons(); 
		?>
		<li data-data_id="<?=$details['tvdb_id']?>" class="container">
			<div class="image">
				<img height="203px" width="138px" src="<?=$tv->get_thumbnail()?>" />
				<? if ($tv->isCaughtUp()): ?>
					<img src="/view/images/banner_watched_2.png" class="banner_watched">
				<? endif; ?>
			</div>
			<div class="title"><span><?=$details['title']?></span></div>
			<div class="date">
				<div class="air_date">
					<span class="day"><?=$details['air_day']?></span>
					<span class="time"><?=$details['air_time']?></span>
				</div>
			</div>
			<!--
			<div class="details">
				<?=$details['overview']?>
			</div>
			-->				
			<div class="download">
				<a href="#" class="button download all" data-setting="<?=$tv->getAutoShowsSetting()?>">
					<span>All Episodes <div style="font-size:10px; margin-top:-10px;">(auto download)</div></span>
				</a>
				<a href="#" class="button download new" data-setting="<?=$tv->getAutoShowsSetting()?>">
					<span>New Episodes <div style="font-size:10px; margin-top:-10px;">(auto download)</div></span>
				</a>
			</div>
			<ul class="sublist">
				<? while (false /*$season = $tv->getSeason()*/): ?>
					<li class="season" data-season="<?=$season['season']?>">
						<a href="#season-<?=$season['season']?>" class="season_title" data-season="<?=$season['season']?>">Season <?=$season['season']?></a>
						<ul>
							<? while (false /*$episode = $tv->getEpisode()*/): ?>
								<li class="episode container" data-data_id="<?=$episode->details['tvdb_id']?>">
									<div class="image">
										<img height="100px" src="" data-src="<?=$episode->get_thumbnail_episode()?>" style="z-index:5;position:relative;" />
										<? if ($episode->hasBeenWatched()): ?>
											<img src="/view/images/banner_watched_2.png" class="banner_watched">
										<? endif; ?>
									</div>
									<div class="download">
										<a href="#" class="button download" data-status="<?=$episode->download_status['column']?>">
											<span><?=$episode->download_status['text']?></span>
										</a>
									</div>	
									<div class="episode">
										Season <?=$episode->details['season']?> Episode <?=$episode->details['episode']?>
									</div>
									<div class="title">
										<i><?=$episode->details['title']?></i>
									</div>
									<div class="date">
										<div class="air_date">
											<?=date("F j, Y", $episode->details['first_aired'])?>
										</div>
										<div class="air_time">
											(<?=date("l g:i",$episode->details['first_aired'])?>)
										</div>
									</div>
									<!--
									<div class="extended">
										<div class="description">
											<?=$episode->details['overview']?>
										</div>
									</div>
									-->
								</li>
							<? endwhile; ?>
						</ul>
					</li>
				<? endwhile; ?>
			</ul>
		</li>
	<? endwhile; ?>
</ul>