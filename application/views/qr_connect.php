
<div class="container center">
    <div class="row">
        <button class="btn btn-danger" onclick="show();">Whatsapp</button>
    </div>
    <div class="row">
        <button class="btn btn-danger" onclick="show();">Telegram</button>
    </div>
    <div class="row">
        <button class="btn btn-danger" onclick="show();">Facebook</button>
    </div>
</div>
<style>
    @media screen and (max-width: 768px) {
        #showPhone {display: block;}
    }
</style>
<script>
    let apiUrl='<?=base_url('api/modal');?>';
    function show(){
        $('#myModal').modal('show');
        fetch(apiUrl+'/open')
            .then(response=>response.json())
            .then(data=>{ console.log(data); })
    }
</script>