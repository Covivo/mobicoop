<template>
  <v-btn
    :color="this.color"
    rounded
  >
    <template v-slot:default>
      <span style="text-transform : none;">
        <slot>  
          default
        </slot>
      </span>
    </template>
  </v-btn>
</template>

<script>
export default {
  props: {
    color: {
      type: String,
      default: "primary"
    },
    textFormat: {
      type: String,
      default: ""
    }
  },
  data() {
    return {};
  }
};
</script>