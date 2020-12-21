<template>
  <div>
    <v-row>
      <v-col
        class="text-left mt-16"
        cols="6"
      >
        <p class="success--text text-h4 font-weight-black">
          Mobicoop c’est bien plus que du covoiturage
        </p>
      </v-col>
      <v-spacer />
    </v-row>
    <v-row justify="center">
      <MFeaturesItem
        v-for="(item, index) in items"
        :key="index"
        :title="item.title"
        :text="item.text"
      />
    </v-row>
  </div>
</template>
<script>
import MFeaturesItem from "@components/utilities/MFeaturesItem";
export default {
  components: {
    MFeaturesItem
  },
  props: {
    title: {
      type: String,
      default: ""
    },
    text: {
      type: String,
      default: ""
    },
    items:{
      type: Array,
      default: null
    }
  }    
}
</script>
<style lang="scss" scoped>
</style>