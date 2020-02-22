<template>
  <v-container
    fluid
  >
    <v-row justify="center">
      <v-col
        cols="0"
        lg="2"
        class="backgroundImageColumn"
        :style="(backgroundImageLeft) ? 'background-image:url(\''+backgroundImageLeft+'\');background-position:top center;' : ''"
      />
      <v-col
        cols="10"
        lg="8"
      >
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
      </v-col>
      <v-col
        cols="0"
        lg="2"
        class="backgroundImageColumn"
        :style="(backgroundImageRight) ? 'background-image:url(\''+backgroundImageRight+'\');background-position:top center;' : ''"
      />
    </v-row>
  </v-container>  
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
    backgroundImageLeft:{
      type:String,
      default:null
    },
    backgroundImageRight:{
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
<style lang="scss" scoped>
.backgroundImageColumn{
  background-size:contain;
}
</style>