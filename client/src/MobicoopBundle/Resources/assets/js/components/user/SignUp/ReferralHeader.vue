<template>
  <div>{{ referral }}</div>
</template>
<script>
export default {
  props: {
    referral: {
      type: String,
      default: null,
    }
  }
}
</script>
