Vue.component('dashboard',{
    template: '#dashboard-template',
    data: function(){
        return {
            players:[],
            orderByField: 'created_at',
            orderByDirection: 1,
            perPage: 35,
            currentPage: 1,
            selectedPlayer: {},
        }
    },
    created: function(){
        this.loadPlayers();
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
                });
            }
        }
    }
});

new Vue({
    el: 'body'
});