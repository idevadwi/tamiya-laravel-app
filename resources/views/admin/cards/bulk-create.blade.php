@extends('adminlte::page')

@section('title', 'Bulk Create Cards')

@section('content_header')
<h1>Bulk Create Cards (Master Data)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Batch Input</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{ route('admin.cards.bulk-store') }}" method="POST">
                @csrf
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="card_codes">Card Codes (One per line)</label>
                        <textarea class="form-control" id="card_codes" name="card_codes" rows="10"
                            placeholder="Enter card codes here, one per line or separated by commas..."
                            required></textarea>
                        <small class="form-text text-muted">Duplicates will be skipped automatically.</small>
                    </div>

                    <div class="form-group">
                        <label for="status">Initial Status</label>
                        <select class="form-control" name="status">
                            <option value="ACTIVE" selected>ACTIVE</option>
                            <option value="LOST">LOST</option>
                            <option value="BANNED">BANNED</option>
                        </select>
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Create Cards</button>
                    <a href="{{ route('admin.cards.index') }}" class="btn btn-default float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop