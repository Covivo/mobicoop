<template>
  <v-content :style="'background-image:url(\''+backgroundImage+'\');background-position:top right;'">
    <v-row
      justify="center"
    >
      <v-col
        cols="6"
        class="text-center"
      >
        <h1 class="text-uppercase text--primary">
          {{ title }}
        </h1>
        <h3 class="headline">
          {{ subtitle }}
        </h3>
      </v-col>
    </v-row>
    <v-row justify="center">
      <MFeaturesItem
        v-for="(item, index) in items"
        :key="index"
        :title="item.title"
        :subtitle="item.subtitle"
        :image="item.image"
        :width="210"
      />
    </v-row>  
  </v-content>  
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
    subtitle: {
      type: String,
      default: ""
    },
    items:{
      type: Array,
      default: null
    },
    backgroundImage:{
      type:String,
      default:null
    }
  }    
}
</script>