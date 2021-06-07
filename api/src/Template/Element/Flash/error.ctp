<?php
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<script>
    $(function () {
        new PNotify({
            title: 'Error',
            text: '<?= $message ?>',
            type: 'error',
            styling: 'bootstrap3'
        });
    })
</script>