Vue.component('dashboard',{
    template: '#dashboard-template',
    data: function(){
        return {
            players:[],
            positions:[{abbr: 'All', selected: true}],
            league: {},
            orderByField: 'points_above_replacement',
            orderByDirection: -1,
            selectedPlayer: {},
            universeErrors: [],
            filterByTaken: true,
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
        calculateValues: function(){
            var vm = this;
            this.$http.get('calc_values').then(function(data){
                vm.loadPlayers();
            });
        },
        updateOrderBy: function(col, dir){
            var default_direction = 1;
            if (dir != null){
                default_direction = dir;
            }
            if (col == this.orderByField){
                this.orderByDirection = -1 * this.orderByDirection;
            }else{
                this.orderByDirection = default_direction;
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
        filterPlayersByTaken: function(p) {
            return !this.filterByTaken || p.taken == 0;
        },
        filterPlayersByPosition: function(p){
            var include;
            var allPos = this.positions.find(function(d){ return d.abbr == 'All' });
            var playerPos = this.positions.find(function(d){ return d.abbr == p.position });
            return allPos.selected || playerPos.selected;
        },
        takePlayer: function(p){
            var vm = this;
            this.$http.get('players/' + p.slug + '/take').then(function(data){
                vm.loadPlayers();
                vm.selectPlayer(p);
            });
            return false;
        },
        untakePlayer: function(p){
            var vm = this;
            this.$http.get('players/' + p.slug + '/untake').then(function(data){
                vm.loadPlayers();
                vm.selectPlayer(p);
            });
            return false;
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
            var msg = '';
            this.universeErrors = [];
            var universe = this.players.filter(function(p){
                return p.universe == 1;
            });
            var qb = universe.filter(function(p){
                return p.position == "QB";
            });
            if (this.league.count_qb != qb.length){
                if (this.league.count_qb > qb.length){
                    msg = 'Not enough';
                }else{
                    msg = 'Too many';
                }
                msg += ' Quarter Backs ' + this.league.count_qb + ' vs ' + qb.length;
                this.universeErrors.push(msg);
            }
            var rb = universe.filter(function(p){
                return p.position == "RB";
            });
            if (this.league.count_rb != rb.length){
                if (this.league.count_rb > rb.length){
                    msg = 'Not enough';
                }else{
                    msg = 'Too many';
                }
                msg += ' Running Backs ' + this.league.count_rb + ' vs ' + rb.length;
                this.universeErrors.push(msg);
            }
            var wr = universe.filter(function(p){
                return p.position == "WR";
            });
            if (this.league.count_wr != wr.length){
                if (this.league.count_wr > wr.length){
                    msg = 'Not enough';
                }else{
                    msg = 'Too many';
                }
                msg += ' Wide Receivers ' + this.league.count_wr + ' vs ' + wr.length;
                this.universeErrors.push(msg);
            }
            var te = universe.filter(function(p){
                return p.position == "TE";
            });
            if (this.league.count_te != te.length){
                if (this.league.count_te > te.length){
                    msg = 'Not enough';
                }else{
                    msg = 'Too many';
                }
                msg += ' Tight Ends ' + this.league.count_te + ' vs ' + te.length;
                this.universeErrors.push(msg);
            }
            var d = universe.filter(function(p){
                return p.position == "D-ST";
            });
            if (this.league.count_d_st != d.length){
                if (this.league.count_d_st > d.length){
                    msg = 'Not enough';
                }else{
                    msg = 'Too many';
                }
                msg += ' Defenses ' + this.league.count_d_st + ' vs ' + d.length;
                this.universeErrors.push(msg);
            }
            var k = universe.filter(function(p){
                return p.position == "K";
            });
            if (this.league.count_k != k.length){
                if (this.league.count_k > k.length){
                    msg = 'Not enough';
                }else{
                    msg = 'Too many';
                }
                msg += ' Kickers ' + this.league.count_k + ' vs ' + k.length;
                this.universeErrors.push(msg);
            }
        },
    }
});

new Vue({
    el: 'body'
});