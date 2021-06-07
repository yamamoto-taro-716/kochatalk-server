<?php
/* @var \App\View\AppView $this */
/* @var \App\Model\Entity\RandomConfig[] $randomConfigs */
$this->assign("title", __("ランダムチャット送信"));
?>
<div class="right_col" role="main">
	<div class="">
		<div class="page-title">
			<div class="title_left">
				<h3><?= $this->fetch("title") ?></h3>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="row">
			<div class="col-xs-12">
				<div class="x_panel">
					<div class="x_title">
						<h2><?= __("ランダムチャット送信") ?></h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						<div class="table-responsive">
							<table class="table table-striped jambo_table bulk_action">
								<thead>
								<tr class="headings">
									<th class="column-title"><?= __("パターン") ?></th>
									<th class="column-title"><?= __("登録日時設定") ?></th>
									<th class="column-title"><?= __("最終アクセス") ?></th>
									<th class="column-title"><?= __("受信数") ?></th>
									<th class="column-title">#</th>
								</tr>
								</thead>
								<tbody>
								<?php foreach ($randomConfigs as $k => $config) : ?>
									<tr>
										<td><?= $config->title ?></td>
										<td><?= $config->created_type == \App\Model\Entity\RandomConfig::TYPE_ALL ? \App\Model\Entity\RandomConfig::getTypeArray()[$config->created_type] : $config->created_value ?></td>
										<td><?= $config->access_type == \App\Model\Entity\RandomConfig::TYPE_ALL ? \App\Model\Entity\RandomConfig::getTypeArray()[$config->access_type] : $config->access_value ?></td>
										<td><?= $config->random_limit ?></td>
										<td>
                                            <a href="<?= $this->Url->build(["action" => "edit", $config->id]); ?>" class="btn btn-warning btn-xs">
                                                <i class="fa fa-pencil-square-o"></i>
                                            </a>
                                        </td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>