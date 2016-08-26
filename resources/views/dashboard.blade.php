@extends('template')

@section('scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.26/vue.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/0.9.3/vue-resource.min.js"></script>
<script type="text/javascript" src="js/dashboard.vue.js"></script>
@endsection

@section('content')
@include('nav', ['activelink' => 'home'])
<div class="container" style="padding-top:75px;">
    @if (count($user->leagues) > 0)
        <ul class="nav nav-tabs nav-justified">
            @foreach($user->leagues as $league)
            <li{{$league->id == $user->league()->id ? ' class=active' : ''}}><a href="/leagues/{{$league->slug}}/setactive">{{$league->name}}</a></li>
            @endforeach
        </ul>
    @else
        <h4><a href="/leagues/new">Create a league</a>, if you want to run a draft.</h4>
    @endif
    @if ($user->league())
        <a href="/leagues/{{$user->league()->slug}}/edit" class="btn btn-primary">Edit {{$user->league()->name}}</a>
        <a href="/leagues/new" class="btn btn-primary">New League</a>
    @endif
    <dashboard></dashboard>
    <template id="dashboard-template">
        <table class="table table-striped">
            <thead>
                <th>Rank</th>
                <th>Team</th>
                <th>Name</th>
                <th>Pos</th>
            </thead>
            <tbody>
                <tr v-for="player in players | orderBy orderByField orderByDirection | limitBy perPage start">
                    <td>
                        @{{player.attributes.espn_rank}}
                    </td>
                    <td>
                        @{{player.espn_abbr}}
                    </td>
                    <td>
                        @{{player.first_name}} @{{player.last_name}}
                    </td>
                    <td>
                        @{{player.position}}
                    </td>
                </tr>
            </tbody>
            <tfoot v-show="players.length > perPage">
                <tr>
                    <td colspan="5">Page:
                        <span v-for="index in (Math.ceil(players.length / 10))">
                            <button class="btn btn-default btn-sm" v-show="index + 1 == currentPage" disabled>@{{ index + 1 }}</button>
                            <span v-else><button class="btn btn-primary btn-sm" @click="currentPage = index + 1">@{{index + 1}}</button></span>
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </template>
</div>
@endsection