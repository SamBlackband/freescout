@if (session('flash_success') || session('flash_success_unescaped'))
    <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {{ session('flash_success') }}{!! safe_raw_html(session('flash_success_unescaped')) !!}
    </div>
@endif
@if (session('flash_warning'))
    <div class="alert alert-warning">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {{ session('flash_warning') }}
    </div>
@endif
@if (session('flash_error'))
    <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {{ session('flash_error') }}
    </div>
@endif
@if (session('flash_error_unescaped'))
    <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {!! safe_raw_html(session('flash_error_unescaped')) !!}
    </div>
@endif

@php
    $handled_last_render_error = null;
    if (Auth::user() && Auth::user()->isAdmin()) {
        $handled_last_render_error = \App\Option::get('handled_last_conversation_render_error', []);
        if (!is_array($handled_last_render_error) || empty($handled_last_render_error)) {
            $handled_last_render_error = null;
        }
    }
@endphp

@if ($handled_last_render_error)
    <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <strong>{{ __('Latest conversation render error') }}</strong><br>
        {{ $handled_last_render_error['captured_at'] ?? __('Unknown time') }}
        @if (!empty($handled_last_render_error['exception_message']))
            — {{ $handled_last_render_error['exception_message'] }}
        @endif
        <br>
        <small>
            {{ __('Conversation') }}: {{ $handled_last_render_error['conversation_id'] ?? '—' }}
            · {{ __('Template') }}: {{ $handled_last_render_error['template'] ?? '—' }}
            · {{ __('File') }}: {{ $handled_last_render_error['exception_file'] ?? '—' }}:{{ $handled_last_render_error['exception_line'] ?? '—' }}
        </small>
        <details style="margin-top:8px;">
            <summary>{{ __('Show render diagnostics') }}</summary>
            <pre style="margin-top:8px; white-space:pre-wrap;">{{ json_encode($handled_last_render_error, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </details>
    </div>
@endif

{{-- Floating flash messages are displayed in layout --}}
@php
    $flashes = \Eventy::filter('flash_messages.flashes', $flashes ?? []);
@endphp

@if (!empty($flashes) && is_array($flashes))
    @foreach ($flashes as $flash)
     	<div class="alert alert-{{ $flash['type'] }}">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
     		@if (!empty($flash['unescaped']))
        		{!! safe_raw_html($flash['text']) !!}
        	@else
        		{{ $flash['text'] }}
        	@endif
    	</div>
    @endforeach
@endif
