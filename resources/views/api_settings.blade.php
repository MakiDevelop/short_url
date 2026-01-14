@extends('layouts.master')

@section('content')
<div class="row mt-5">
    <div class="col-md-8 offset-md-2">
        <h2>API Settings</h2>
        <hr>

        <div class="card">
            <div class="card-header">
                <h5>API Token</h5>
            </div>
            <div class="card-body">
                <p>Use this token to authenticate API requests. Include it in the <code>Authorization</code> header:</p>
                <pre class="bg-light p-2">Authorization: Bearer YOUR_API_TOKEN</pre>

                <div class="form-group">
                    <label for="apiToken">Your API Token:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="apiToken"
                               value="{{ $user->api_token ?? 'No token generated yet' }}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToken()">Copy</button>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary" onclick="generateToken()">
                    {{ $user->api_token ? 'Regenerate Token' : 'Generate Token' }}
                </button>
                <small class="text-muted d-block mt-2">
                    Warning: Regenerating will invalidate your existing token.
                </small>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>API Documentation</h5>
            </div>
            <div class="card-body">
                <h6>Base URL</h6>
                <pre class="bg-light p-2">{{ url('/api/v1') }}</pre>

                <h6 class="mt-4">Endpoints</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>Description</th>
                            <th>Auth</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td>/urls</td>
                            <td>Create short URL</td>
                            <td>Optional</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td>/urls/{code}</td>
                            <td>Get URL info</td>
                            <td>Optional</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td>/urls/{code}/analytics</td>
                            <td>Get URL analytics</td>
                            <td>Optional</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td>/urls</td>
                            <td>List your URLs</td>
                            <td>Required</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-danger">DELETE</span></td>
                            <td>/urls/{code}</td>
                            <td>Delete URL</td>
                            <td>Required</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td>/user</td>
                            <td>Get user info</td>
                            <td>Required</td>
                        </tr>
                    </tbody>
                </table>

                <h6 class="mt-4">Example: Create Short URL</h6>
                <pre class="bg-dark text-light p-3"><code>curl -X POST {{ url('/api/v1/urls') }} \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{"url": "https://example.com", "title": "Example"}'</code></pre>

                <h6 class="mt-4">Response</h6>
                <pre class="bg-dark text-light p-3"><code>{
  "success": true,
  "data": {
    "code": "abc12345",
    "short_url": "{{ url('/') }}/abc12345",
    "original_url": "https://example.com",
    "title": "Example",
    "created_at": "2024-01-14T12:00:00.000000Z"
  }
}</code></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_script')
<script>
function generateToken() {
    if (!confirm('Are you sure? This will invalidate your existing token.')) {
        return;
    }

    $.ajax({
        url: '/api/generate-token',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#apiToken').val(response.api_token);
                alert('Token generated successfully!');
            }
        },
        error: function() {
            alert('Failed to generate token. Please try again.');
        }
    });
}

function copyToken() {
    var tokenInput = document.getElementById('apiToken');
    tokenInput.select();
    document.execCommand('copy');
    alert('Token copied to clipboard!');
}
</script>
@endsection
