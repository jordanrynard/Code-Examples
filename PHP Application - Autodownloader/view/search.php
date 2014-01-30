<form class="search_form" action="" method="post">
	<input type="text" name="query" placeholder="Search"/>
<? if (!empty($_POST['query']) && isset($shows_movies_episodes) && empty($shows_movies_episodes)): ?>
	<h1>
		No results for "<?=$_POST['query']?>"
	</h1>
<? endif; ?>
<? if (!empty($shows_movies_episodes)): ?>
	<div class="heading">Showing Results for: <i><?=$_POST['query']?></i></div>
<? endif; ?>
	<div class="please-wait">
		<h1>Please Wait</h1>
		<img src="<?=URL?>/view/images/loading-large.gif" />
	</div>
</form>
