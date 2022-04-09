
<div class="container">
    <button class="btn btn-danger" onclick="show();">Click</button>
    <div style="text-align: center;display: none" id="showPhone">
        <img src="<?=image_url('phone.jpg') ?>" width="40%">
    </div>

    <div id="myModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                    <h4>Please scan this QR code</h4>
                </div>
                <div class="modal-body">
                    <img src="<?=image_url('popup.jpg') ?>" width="100%">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let apiUrl='<?=base_url('api/modal');?>';
    function show(){
        $('#myModal').modal('show');
        fetch(apiUrl+'/open')
            .then(response=>response.json())
            .then(data=>{ console.log(data); })
    }

    setInterval(function (){
        if ($('#myModal').hasClass('in')){
            fetch(apiUrl)
                .then(response=>response.json())
                .then(data=>{
                    if (data.status=='closed'){
                        $('#myModal').modal('hide');
                        $('#showPhone').show();
                    }

                    console.log(data);
                })
        }
    },1000);

</script>