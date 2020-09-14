<template>
  <div>
    <iframe
      :height="height"
      :width="height"
      :src="src"
      :frameborder="frameborder"
    />
  </div>
</template>
<script>

export default {
  name: "MFluxRss",
  components: {
  },
  props: {
    src: {
      type: String,
      default: ""
    },
    height: {
      type: String,
      default: ""
    },
    width: {
      type: String,
      default: ""
    },
    frameborder: {
      type: String,
      default: ""
    },
  },
  data() {
    return {
      loading:true
    }
  },
}
</script>