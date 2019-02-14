<template>
    <i class="fa fa-spin fa-spinner" v-if="init_load"></i>
    <span class="nm-color-light-red" v-else> ({{counter}})</span>
</template>
<script>
    import axios from 'axios';

    export default {

        name: "count-live",
        data() {
            return {
                counter: 0,
                init_load: true,
            }
        },
        methods:{
          loadData: async function(){
              this.init_load = true;
              try {
                  const response = await axios.get('/_ajax/_count_live');
                  this.counter = response.data.extra.counter;
                  this.init_load = false;
              } catch (e) {
                  this.errors.push(e);
                  this.init_load = false;
                  this.counter = '?';
              }
          }
        },
        async created() {
            this.loadData();

            setInterval(function () {
                this.loadData();
            }.bind(this), 60000);
        }
    }
</script>

<style scoped>

</style>