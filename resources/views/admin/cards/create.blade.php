@extends('adminlte::page')

@section('title', 'Create Card')

@section('content_header')
<h1>Create New Card (Master Data)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Card Details</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{ route('admin.cards.store') }}" method="POST">
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
                        <label for="card_code">Card Code</label>
                        <input type="text" class="form-control @error('card_code') is-invalid @enderror" id="card_code"
                            name="card_code" placeholder="Scan or enter card code" value="{{ old('card_code') }}"
                            required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="racer_id">Assign to Racer (Optional)</label>
                        <select class="form-control select2 @error('racer_id') is-invalid @enderror" id="racer_id"
                            name="racer_id">
                            <option value="">Unassigned</option>
                            @foreach($racers as $racer)
                                <option value="{{ $racer->id }}" {{ old('racer_id', request('racer_id')) == $racer->id ? 'selected' : '' }}>
                                    {{ $racer->racer_name }}
                                    @if($racer->team)
                                        ({{ $racer->team->team_name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" name="status">
                            <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                            <option value="LOST" {{ old('status') == 'LOST' ? 'selected' : '' }}>LOST</option>
                            <option value="BANNED" {{ old('status') == 'BANNED' ? 'selected' : '' }}>BANNED</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="coupon">Initial Coupons</label>
                        <input type="number" class="form-control @error('coupon') is-invalid @enderror" id="coupon"
                            name="coupon" value="{{ old('coupon', 0) }}" min="0">
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Create Card</button>
                    <a href="{{ route('admin.cards.index') }}" class="btn btn-default float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
    });
</script>
@stop