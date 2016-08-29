@extends('template')

@section('scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.26/vue.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/0.9.3/vue-resource.min.js"></script>
<script type="text/javascript" src="js/dashboard.vue.js"></script>
<script type="text/javascript">
    var token = '{{csrf_token()}}';
</script>
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
        <div class="draft-container">
            <div class="player-table-container">
                <div class="filter-positions">
                    <button class="btn btn-sm" v-for="p in positions" @click="selectPosition(p)" v-bind:class="{ 'btn-default': !p.selected, 'btn-primary': p.selected}">@{{p.abbr}}</button>
                </div>
                <table class="table table-striped table-players">
                    <thead>
                        <th>Rank</th>
                        <th>Team</th>
                        <th>Name</th>
                        <th>Pos</th>
                        @if ($user->league())
                            <th>Uni</th>
                        @endif
                    </thead>
                    <tbody>
                        <tr @click="selectPlayer(player)" v-for="player in players | orderBy orderByField orderByDirection | filterBy filterPlayersByPosition">
                            <td>
                                @{{player.attributes.espn_rank}}
                            </td>
                            <td>
                                @{{player.espn_abbr}}
                            </td>
                            <td>
                                <a href="#">@{{player.first_name}} @{{player.last_name}}</a>
                            </td>
                            <td>
                                @{{player.position}}
                            </td>
                            @if ($user->league())
                                <td>
                                    @{{player.universe == 1 ? 'Yes' : 'No'}}
                                </td>
                            @endif
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
            </div>
            <div class="player-info" v-show="selectedPlayer.first_name">
                @{{selectedPlayer.first_name + ' ' + selectedPlayer.last_name}}<br>
                In Universe: <input type="checkbox" class="form-control in_universe" @click="toggleUniverse()" v-model="selectedPlayer.in_universe" />
                <div class="universe_status alert" v-bind:class="{ 'alert-success': universeStatus.complete, 'alert-danger': universeStatus.error }">@{{universeStatus.msg}}</div>     
            </div>       
        </div>
    </template>
</div>
@endsection