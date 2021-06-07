<?php
/* @var \App\View\AppView $this */
$this->assign("title", __("Profile"));
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
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Info</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <br>
                        <?= $this->Form->create($user, ["id" => "frmSetting", "class" => "form-horizontal form-label-left"]); ?>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                Username
                            </label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <?= $this->Form->control("username", ["label" => false, "class" => "form-control"]) ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                Password
                            </label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <?= $this->Form->control("password", ["label" => false, "class" => "form-control"]) ?>
                            </div>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </div>
                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>