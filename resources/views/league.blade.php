@extends('template')

@section('content')
@include('nav', ['activelink' => ''])
<div class="container" style="padding-top:75px;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3>{{isset($league) ? 'Edit '.$league->name : 'New League'}}</h3>
        </div>
        <div class="panel-body">
            <form method="post">
                {{csrf_field()}}
                <div class="form-group row">
                    <label class="form-label control-label col-xs-12 col-sm-2" for="name">Name</label>
                    <div class="col-xs-12 col-sm-10">
                        <input class="form-control" name="name" value="{{isset($league) ? $league->name : ''}}" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="form-label control-label col-xs-12 col-sm-2" for="name">Number of Teams</label>
                    <div class="col-xs-12 col-sm-10">
                        <input class="form-control" name="team_count" value="{{isset($league) ? $league->attribute('team_count') : ''}}" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-6">
                        @foreach($scorings as $s)
                        <div class="row">
                            <label class="form-label control-label col-xs-12 col-sm-6" for="name">Points per {{$s['name']}}</label>
                            <div class="col-xs-12 col-sm-6">
                                <input class="form-control" name="{{$s['slug']}}" value="{{isset($league) ? $league->attribute($s['slug']) : ''}}" />
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        @foreach(\App\Models\Position::all() as $position)
                        <div class="row">
                            <label class="form-label control-label col-xs-12 col-sm-6" for="name">Number of {{strtoupper($position->slug)}}s</label>
                            <div class="col-xs-12 col-sm-6">
                                <input class="form-control" name="{{$position->slug}}" value="{{isset($league) ? $league->attribute('count_'.$position->slug) : ''}}" />
                            </div>
                        </div>
                        @endforeach
                        <div class="row">
                            <label class="form-label control-label col-xs-12 col-sm-6" for="name">Number of Bench spots</label>
                            <div class="col-xs-12 col-sm-6">
                                <input class="form-control" name="count_bench" value="{{isset($league) ? $league->attribute('count_bench') : ''}}" />
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection