<template>
    <div class="col-sm-2 col-12">
        <div class="row justify-content-center" v-if="loading">
            <div class="col-12" style="height:60px;">
                <i class="fa fa-spin fa-spinner fa-2x"></i>
            </div>
        </div>
        <div class="row justify-content-center" v-if="champions && champions.length">
            <div class="col-3" v-for="champion of champions">
                <a class="liveChamp" :href="champion.watch">
                    <img v-tooltip="champion.title" :src="champion.url" style="max-height:50px;" class="rounded-circle" :alt="champion.name" />
                </a>
            </div>
        </div>
        <div class="row justify-content-center" v-if="errors && errors.length">
            <div class="col-12" v-for="error of errors">
                {{error.message}}
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "recent-live",
        data() {
            return {
                champions: [],
                errors: [],
                loading: true,
            }
        },
        async created() {
            try {
                const response = await axios.get('/_ajax/_recent_live');
                this.champions = response.data.extra;
                this.loading = false;
            } catch (e) {
                this.errors.push(e);
                this.loading = false;
            }
        }
    }
</script>

<style scoped>
</style>