<div class="row-fluid">
    {!! form_open(current_url(),
        [
            'id'     => 'form-widget',
            'role'   => 'form',
            'method' => 'POST',
        ]
    ) !!}

       {!! $this->renderForm() !!}

    {!! form_close() !!}
</div>