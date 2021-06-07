<?php
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<script>
    $(function () {
        new PNotify({
            title: 'Information',
            text: '<?= $message ?>',
            type: 'success',
            styling: 'bootstrap3'
        });
    })
</script>