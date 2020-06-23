@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                        <synchronized-platforms></synchronized-platforms>
                        <hr>
                        <passport-clients></passport-clients>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
