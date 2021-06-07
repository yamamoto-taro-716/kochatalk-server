<?php
/* @var \App\View\AppView $this */
$this->assign("title", __("設定"));
echo $this->Html->script("https://cdn.ckeditor.com/4.9.2/standard/ckeditor.js");
?>
<style>
    .checkbox{
        margin-top: 0;
    }
</style>
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
                        <h2>設定</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <br>
                        <?= $this->Form->create($setting, ["id" => "frmSetting", "class" => "form-horizontal form-label-left"]); ?>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                    Delete Messages after (day)
                                </label>
                                <div class="col-md-3 col-sm-3 col-xs-12">
                                    <?= $this->Form->control("day_message", ["label" => false, "class" => "form-control"]) ?>
                                </div>
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                    Number time show AdMob
                                </label>
                                <div class="col-md-3 col-sm-3 col-xs-12">
		                            <?= $this->Form->control("count_ads", ["label" => false, "class" => "form-control"]) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                    Telop (JA)
                                </label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
		                            <?= $this->Form->control("title_ads", ["label" => false, "class" => "form-control"]) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                    Telop (EN)
                                </label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <?= $this->Form->control("title_ads_en", ["label" => false, "class" => "form-control"]) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                    Announcement (JA)
                                </label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <?= $this->Form->control("content_ads", ["label" => false, "class" => "form-control"]) ?>
                                    <script>
                                        CKEDITOR.replace( 'content-ads' );
                                    </script>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                    Announcement (EN)
                                </label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <?= $this->Form->control("content_ads_en", ["label" => false, "class" => "form-control"]) ?>
                                    <script>
                                        CKEDITOR.replace( 'content-ads-en' );
                                    </script>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                    Term of use (JA)
                                </label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <?= $this->Form->control("term_ja", ["label" => false, "class" => "form-control"]) ?>
                                    <script>
                                        CKEDITOR.replace( 'term_ja' );
                                    </script>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                    Term of use (EN)
                                </label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <?= $this->Form->control("term_en", ["label" => false, "class" => "form-control"]) ?>
                                    <script>
                                        CKEDITOR.replace( 'term_en' );
                                    </script>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                    Policy (JA)
                                </label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <?= $this->Form->control("policy_ja", ["label" => false, "class" => "form-control"]) ?>
                                    <script>
                                        CKEDITOR.replace( 'policy_ja' );
                                    </script>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                                    Policy (EN)
                                </label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <?= $this->Form->control("policy_en", ["label" => false, "class" => "form-control"]) ?>
                                    <script>
                                        CKEDITOR.replace( 'policy_en' );
                                    </script>
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