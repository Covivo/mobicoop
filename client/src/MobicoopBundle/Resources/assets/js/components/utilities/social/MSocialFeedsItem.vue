<template>
  <div v-html="iFrameString" />
</template>
<script>
export default {
  props:{
    iFrameString:{
      type: String,
      default: null
    }
  }
}
</script>