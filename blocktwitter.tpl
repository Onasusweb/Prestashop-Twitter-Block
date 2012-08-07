<!-- Block Twitter -->
<div style='margin-left: auto; margin-right: auto;width: {$config.PS_BT_WIDTH}px;'>
	<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
	<script>
	var tw = {$config.PS_BT_WIDTH} - 10;
	new TWTR.Widget({
	  version: 2,
	  type: 'profile',
	  rpp: 10,
	  interval: 30000,
	  width: tw,
	  height: {$config.PS_BT_HEIGHT},
	  theme: {
		shell: {
		  background: '{$config.PS_BT_SHELL_BG}',
		  color: '{$config.PS_BT_SHELL_COLOR}'
		},
		tweets: {
		  background: '{$config.PS_BT_TWEET_BG}',
		  color: '{$config.PS_BT_TWEET_COLOR}',
		  links: '{$config.PS_BT_TWEET_LINK}'
		}
	  },
	  features: {
		scrollbar: false,
		loop: false,
		live: true,
		behavior: 'default'
	  }
	}).render().setUser('{$config.PS_BT_USERNAME}').start();
	</script>
</div>
<!-- /Block Twitter -->