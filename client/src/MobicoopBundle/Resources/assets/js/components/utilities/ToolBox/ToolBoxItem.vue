<template>
  <v-card flat>
    <div class="d-flex flex-no-wrap justify-space-between">
      <div>
        <v-row align="center">
          <v-col
            cols="6"
            class="text-left"
          >
            <v-avatar
              class="ma-0"
              size="125"
              tile
            >
              <v-img :src="image" />
            </v-avatar>
          </v-col>
          <v-col
            cols="6"
            class="text-left"
          >
            <h4
              class="primary--text text-left font-weight-bold"
            >
              {{ title }}
            </h4>
            <a
              style="text-decoration:none;"
              class="primary--text"

              :href="downloadLink"
              :title="textDownload"
            >
              {{ textDownload }}        
            </a>

            <a
              class="primary--text"
              style="text-decoration:none;"
               
              :href="calameoLink"
              target="_blank"
            >
              {{ subtitle }}
            </a>
            <br>
            <a
              :href="personalizedText"
              :title="personalizedTextLink"
              style="text-decoration:none;"
              class="accent--text"
            >{{ personalizedTextLink }}</a>
            
            <a
              :href="link1"
              :title="textLink1"
              style="text-decoration:none;"
              class="accent--text"
            >{{ textLogo1 }}</a>
            <a
              :href="link2"
              :title="textLink2"
              style="text-decoration:none;"
              class="accent--text"
            >{{ textLogo2 }}</a>
            <a
              :href="link3"
              :title="textLink3"
              style="text-decoration:none;"
              class="accent--text"
            >{{ textLogo3 }}</a>
          </v-col>
        </v-row>
      </div>
    </div>
  </v-card>
</template>

<script>
export default {
  props: {
    image:{
      type: String,
      default: null
    },
    text:{
      type: String,
      default: null
    },
    textDownload:{
      type: String,
      default: null
    },
    downloadLink:{
      type: String,
      default: null
    },
    personalizedText:{
      type: String,
      default: null
    },
    personalizedTextLink:{
      type: String,
      default: null
    },
    link1:{
      type: String,
      default: null
    },
    link2:{
      type: String,
      default: null
    },
    link3:{
      type: String,
      default: null
    },
    textLogo1:{
      type: String,
      default: null
    },
    textLogo2:{
      type: String,
      default: null
    },  
    textLogo3:{
      type: String,
      default: null
    },
    title:{
      type: String,
      default: null
    },
    subtitle:{
      type: String,
      default: null
    },
    LinkSubtitle:{
      type: String,
      default: null
    }
  },
  data() {
    return {
    }
  }
};
</script>
