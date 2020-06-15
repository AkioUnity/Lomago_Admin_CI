<script src="https://cdn.ckeditor.com/4.13.0/standard/ckeditor.js"></script>
<div class="row">
    <div class="col-md-10">
            <form class="form-horizontal" action="auto/text/<?php echo $type ?>" method="post">
                    <?php foreach ($results as $result) { ?>
                        <div class="box box-info">
                            <input type="hidden" name="id[]" value="<?php echo $result->id ?>">
                            <div class="box-header with-border">
                                <label class="col-sm-2 ">Block <?php echo $result->step ?></label>
                                <label class="col-sm-10"
                                       style="font-weight: 500;"><?php echo $result->note ?></label>

                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                    </button>
                                </div>
                                <!-- /.box-tools -->
                            </div>
                            <div class="box-body">
                                <?php if ($result->step == 1){ ?>
                                    <textarea id="editor" name="text[]">
                                        <?php echo $result->text ?>
                                    </textarea>
                                    <script>
                                        CKEDITOR.replace( 'editor' );
                                    </script>
                                <?php }else{ ?>
                                <textarea class="form-control" rows="4"
                                          name="text[]" placeholder="Enter ..."><?php echo $result->text ?></textarea>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>

                    <button type="submit" class="btn btn-info pull-right">Save</button>
            </form>
    </div>
</div>