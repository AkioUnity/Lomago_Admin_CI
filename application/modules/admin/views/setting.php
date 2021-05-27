<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<?php if ($this->session->flashdata('success')) { ?>
    <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <?= $this->session->flashdata('success') ?>
    </div>
<?php } else if($this->session->flashdata('info')){  ?>
    <div class="alert alert-info">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <?php echo $this->session->flashdata('info'); ?>
    </div>
<?php } ?>

<div class="row">
    <div class="col-md-5">
        <?php echo modules::run('adminlte/widget/box_open', 'Set Consultant Name before Messages'); ?>
        <input id="toggle-event" type="checkbox" <?php echo ($setting=='true')?'checked':''; ?> data-toggle="toggle" data-on="<i class='fa fa-toggle-on'></i> On  " data-off="<i class='fa fa-toggle-off'></i> Off" >
        <?php echo modules::run('adminlte/widget/box_close'); ?>
    </div>
</div>

<script>
    $(function() {
        $('#toggle-event').change(function() {
            console.log($(this).prop('checked'));
            window.location='whatsapp/setting?value='+$(this).prop('checked');
        })
    })
</script>
