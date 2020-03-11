<template>
    <div>
        <div style="display: inline-block; margin-right: 2px; margin-left: 12px;">
            <vue-loaders name="ball-scale-ripple" color="#6CE83D"
                         scale="0.5"></vue-loaders>
        </div>

        <div style="display: inline-block; margin-right: 2px;">
            <vue-loaders name="ball-scale-ripple" color="#3d59e8"
                         scale="0.5"></vue-loaders>
        </div>

        <div style="display: inline-block; margin-right: 2px;">
            <vue-loaders name="ball-scale-ripple" color="#3d59e8"
                         scale="0.5"></vue-loaders>
        </div>
    </div>
</template>

<script>
  import VueLoaders from 'vue-loaders/dist/vue-loaders.umd';
  import Vue from 'vue';

  Vue.use(VueLoaders);
  export default {
    name: 'LoaderSet',
  };
</script>

<style>
    @import '~vue-loaders/dist/vue-loaders.css';

</style>
