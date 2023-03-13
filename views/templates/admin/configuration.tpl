{if $message != null}
    <div class="alert alert-success" role="alert">
        {$message}
    </div>
{else}
{/if}

<form action="" method="post">
    <div class="form-group">
        <label class="form-control-label" for="input1">Module Message</label>
        <input type="text" name="first_message" class="form-control" value="{$first_message}" id="input1" required/>
    </div>

    <div class="form-group">
    <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>