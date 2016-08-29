Vue.component('dashboard',{
    template: '#dashboard-template',
    data: function(){
        return {
            players:[],
            positions:[{
                selected: true,
            }],
            league: {},
            orderByField: 'attributes.espn_rank',
            orderByDirection: 1,
            selectedPlayer: {},
            universeStatus: {
                complete: false,
                error: false,
                msg: '',
            },
            playerFilter: {}
        }
    },
    created: function(){
        this.loadPlayers();
        this.loadLeague();
        this.loadPositions();
    },
    computed: {
        start: function(){
            return (this.currentPage - 1) * this.perPage;
        },
    },
    methods: {
        loadPlayers: function(){
            var vm = this;
            this.$http.get('players').then(function(data){
                vm.players = JSON.parse(data.body);
                vm.calculateUniverse();
            });
        },
        loadLeague: function(){
            var vm = this;
            this.$http.get('league').then(function(data){
                vm.league = JSON.parse(data.body);
            });
        },
        loadPositions: function(){
            var vm = this;
            this.$http.get('positions').then(function(data){
                vm.positions = [{abbr: 'All', selected: true}];
                var positions = JSON.parse(data.body)
                for(p of positions){
                    vm.positions.push(p);
                }
            });
        },
        updateOrderBy: function(col){
            if (col == this.orderByField){
                this.orderByDirection = -1 * this.orderByDirection;
            }else{
                this.orderByDirection = 1;
                this.orderByField = col;
            }
            
            return;
        },
        selectPlayer: function(p){
            var vm = this;
            this.$http.get('players/' + p.slug).then(function(data){
                vm.selectedPlayer = JSON.parse(data.body);
            });
            return false;
        },
        selectPosition: function(p){
            for(pos of this.positions){
                pos.selected = false;
            }
            p.selected = true;
        },
        filterPlayersByPosition: function(p){
            var include;
            var allPos = this.positions.find(function(d){ return d.abbr == 'All' });
            var playerPos = this.positions.find(function(d){ return d.abbr == p.position });
            return allPos.selected || playerPos.selected;
        },
        toggleUniverse: function(){
            if (this.selectedPlayer){
                var sent = {
                    _token: token,
                    player: this.selectedPlayer.slug
                };
                var vm = this;
                this.$http.post('toggle_universe', sent).then(function(data){
                    vm.selectedPlayer = JSON.parse(data.body);
                    vm.loadPlayers();
                });
            }
        },
        calculateUniverse: function(){
            this.universeStatus.msg = '';
            var universe = this.players.filter(function(p){
                return p.universe == 1;
            });
            var wr = universe.filter(function(p){
                return p.position == "WR";
            });
            if (this.league.count_wr * this.league.team_count != wr.length){
                this.universeStatus.error = true;
                this.universeStatus.complete = false;
                if (this.league.count_wr * this.league.team_count > wr.length){
                    this.universeStatus.msg += 'Not enough';
                }else{
                    this.universeStatus.msg += 'Too many';
                }
                this.universeStatus.msg += ' Wide Receivers\r\ntest';
            }
        },
    }
});

new Vue({
    el: 'body'
});