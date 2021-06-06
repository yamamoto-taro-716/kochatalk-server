<?php /* @var $this \App\View\AppView */  ?>
<ul class="pagination" style="margin: 5px 0;">
    <li><a href="#">合計: <?= $this->Paginator->param("count") ?></a></li>
    <?php
    echo $this->Paginator->hasPrev() ? $this->Paginator->prev('&laquo;', array('escape' => false, 'tag' => 'li'), null, array('escape' => false, 'disabledTag' => 'a', 'tag' => 'li', 'class' => 'disabled')) : '';
    echo $this->Paginator->numbers(array('separator' => '', 'currentClass' => 'active', 'currentTag' => 'a', 'tag' => 'li'));
    echo $this->Paginator->hasNext() ? $this->Paginator->next('&raquo;', array('escape' => false, 'tag' => 'li'), null, array('escape' => false, 'disabledTag' => 'a', 'tag' => 'li', 'class' => 'disabled')) : '';
    ?>
</ul>