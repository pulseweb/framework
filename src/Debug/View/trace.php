<style>
    *{
        margin: 0;
        padding: 0;
    }
    .error{
        background-color: indigo;
        color: white;
        padding: 1.5rem 2rem;
    }
    .error table{
        color: #ddf;
        width: 100%;
        padding: 2rem;
        background-color: #300153;
        margin: 1rem 0;
        border-spacing: 0 0.5em;
    }
</style>
<div>
    <div class="error">
    <p>Error:<?= $message?></p>
    </div>
    <?php foreach ($trace as $i_trace):?>
    <div class="error">
        
        <p style="font-size: 1.5rem;"><?= $i_trace['file']?><p>
        <table>
            <?php foreach ($i_trace as $key => $value): if(is_array($value)) continue;?>
                <tr>
                    <td><?= $key?><td>
                    <td><?= $value?><td>
                </tr>
            <?php endforeach;?>
        </table>
    </div>
    <?php endforeach;?>
</div>