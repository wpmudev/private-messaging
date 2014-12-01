<?php
$colors = array(
	'igu-blue',
	'igu-pink',
	'igu-dark-blue',
	'igu-green',
	'igu-black',
	'igu-yellow',
	'igu-purple',
	'igu-grey',
	'igu-green-alt',
	'igu-red',
	'igu-marine',
);
$color  = $colors[ array_rand( $colors ) ];
?>
<div class="igu-media-file-land" id="igu-media-file-<?php echo $model->id ?>" data-id="<?php echo $model->id ?>">
	<div class="well well-sm">
		<div class="igu-media-file-thumbnail hidden-xs hidden-sm <?php echo $color ?>">
			<?php echo $model->mime_to_icon() ?>
		</div>
		<div class="igu-media-file-meta">
			<div class="btn-group btn-group-xs">
				<button type="button" class="btn btn-default btn-xs dropdown-toggle popover-anchor-<?php echo $model->id ?>" data-toggle="dropdown"
				        aria-expanded="false" aria-haspopup="true">
					<i class="fa fa-bars"></i>
				</button>
				<ul class="dropdown-menu" role="menu">
					<li>
						<a href="#" data-id="<?php echo $model->id ?>"
						   data-target="#igu-uploader-form-<?php echo $model->id ?>" type="button"
						   class="igu-file-update" data-anchor=".popover-anchor-<?php echo $model->id ?>">
							<?php _e( "Edit", mmg()->domain ) ?>
						</a>
					</li>
					<li>
						<a href="#" data-id="<?php echo $model->id ?>" type="button"
						   class="igu-file-delete">
							<?php _e( "Delete", mmg()->domain ) ?>
						</a>
					</li>
				</ul>
			</div>
			<h5><?php echo mmg()->trim_text( $model->name, 17 ) ?></h5>

			<p class="text-muted small"><?php echo get_the_date( null, $model->id ) ?></p>
		</div>
		<div class="clearfix"></div>
	</div>

</div>