<template>
    <ul class="labels">
        <li class="title mLink">Latest Streams</li>
        <li v-if="loading"><i class="fa fa-spin fa-spinner fa-3x"></i></li>
        <li v-if="streamers && streamers.length" v-for="streamer of streamers">
            <a :href="streamer.link" class="mLink">
                <span class="ls-ball-online tt" title="Online"></span>{{ streamer.streamer }}
                <div :class="streamer.showClass+' ls-in-game'">{{ streamer.inGame }}</div>
            </a>
        </li>
        <li id="clientManage"><a href="/streamer/all" class="italic-link mLink">Moar</a></li>
    </ul>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "latest-streams",
        data() {
            return {
                streamers: [],
                loading: true,
            }
        },
        methods: {
            loadData: async function () {
                try {
                    const response = await axios.get('/_ajax/_latest_streamer');
                    this.streamers = response.data.extra;
                    this.loading = false;
                } catch (e) {
                    this.loading = false;
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