Vue.component('dashboard',{
    template: '#dashboard-template',
    data: function(){
        return {
            players:[],
            orderByField: 'created_at',
            orderByDirection: 1,
            perPage: 10,
            currentPage: 1,
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
    }
});

new Vue({
    el: 'body'
});