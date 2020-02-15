<template>
  <div
    class="items mx-4"
    :style="'width:'+width+'px'"
  >
    <v-row class="text-center">
      <v-col class="text-center">
        <div
          class="backgroundItemsImages"
          :style="'background-color:'+backgroundColorImage"
          align="middle"
        >
          <img
            :src="image"
            class="itemsImages"
          >
        </div>
      </v-col>
    </v-row>
    <v-row class="text-center">
      <v-col class="font-weight-bold title">
        {{ title }}
      </v-col>
    </v-row>
    <v-row class="text-center">
      <v-col>{{ subtitle }}</v-col>
    </v-row>
  </div>
</template>
<script>
export default {
  props: {
    title: {
      type: String,
      default: ""
    },
    subtitle: {
      type: String,
      default: ""
    },
    image:{
      type: String,
      default: ""
    },
    width:{
      type:Number,
      default:400
    },
    backgroundColorImage:{
      type:String,
      default:"#F4F5F6"
    }
  }    
}
</script>
<style lang="scss" scoped>
div.backgroundItemsImages{
    padding:20px;
    border-radius: 100px;
    height:150px;
    width:150px;
    margin:auto auto;
}
</style>