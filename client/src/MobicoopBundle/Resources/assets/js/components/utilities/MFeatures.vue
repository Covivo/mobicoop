<template>
  <v-content :style="'background-image:url(\''+backgroundImage+'\');background-position:top right;'">
    <v-row
      justify="center"
    >
      <v-col
        cols="7"
        class="text-center"
      >
        <v-card-text
          class="display-1 text-uppercase"
          :class="(textThin) ? 'text--'+colorTitle+' font-weight-thin' : 'text--'+colorTitle"
        >
          {{ title }}
        </v-card-text>
        <v-card-text
          class="headline"
          :class="(textThin) ? 'text--'+colorSubtitle+' font-weight-thin' : 'text--'+colorSubtitle"
        >
          {{ subtitle }}
        </v-card-text>
      </v-col>
    </v-row>
    <v-row
      justify="center"
      class="mt-10"
    >
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
    },
    colorTitle:{
      type:String,
      default:"primary"
    },
    colorSubtitle:{
      type:String,
      default:"primary"
    },
    textThin:{
      type:Boolean,
      default:false
    }
  }    
}
</script>