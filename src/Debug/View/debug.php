<style>
    #pulse-debugger{
        position: fixed;
        inset: 1rem;
        background-color: #ccc;
        z-index: 1000;
        border-radius: 1rem;
        padding: 1rem;
        color: #000
    }
    #pulse-debugger *{
        color: #000;
    }

    #pulse-debugger .query_div{
        background-color: #fff;
        margin: 0.5rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
    }
</style>
<div id="pulse-debugger" class="hidden">
    <h1>Queries: <?= count($GLOBALS['queries'])?></h1>
    <?php foreach ($GLOBALS['queries'] as $query) :?>
        <div class="query_div">
            <?= $query ?>
        </div>
    <?php endforeach; ?>
</div>
<script>
    document.addEventListener('keypress', e =>{
        if((e.which == 68 || e.which == 100) && e.shiftKey){
            $("#pulse-debugger").toggleClass('hidden')
        }
    })
</script>