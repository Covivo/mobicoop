<template>
  <v-card
    :class="textColor"
    :color="backgroundColor"
    :height="height"
    :flat="flat"
    :tile="tile"
  >
    <v-row
      dense
    >
      <v-col :style="'height:'+heightTitle+';'">
        <v-card-title
          :class="optionnalTitleClasses"
          v-html="cardTitle"
        />
      </v-col>
    </v-row>
    <v-row
      v-if="cardText"
      dense
    >
      <v-col>
        <v-card-text
          :class="textColor"
          align="center"
        >
          {{ cardText }}
        </v-card-text>
      </v-col>
    </v-row>
    <v-row
      dense
    >
      <v-col>
        <v-card-actions>
          <v-row
            justify="center"
          >
            <v-col 
              :align="alignButton"
            >
              <v-btn
                rounded
                :class="cardTextColorButton+' '+cardColorButton +' '+classButton"
                :href="hrefButton"
                :width="sizeButton"
              >
                {{ cardTextButton }}
              </v-btn>
              <v-btn
                v-if="cardTextButtonTwo!==''"
                rounded
                class="ma-0 text-body-2"
                :class="cardTextColorButtonTwo+' '+cardColorButtonTwo"
                :href="hrefButtonTwo"
                :width="sizeButton"
              >
                {{ cardTextButtonTwo }}
              </v-btn>
            </v-col>
          </v-row>
        </v-card-actions>
      </v-col>
    </v-row>
  </v-card>
</template>

<script>
export default {
  props: {
    cardTitle: {
      type: String,
      default: ""
    },
    cardText: {
      type: String,
      default: ""
    },    
    backgroundColor: {
      type: String,
      default: "secondary"
    },    
    textColor: {
      type: String,
      default: "white--text"
    }, 
    classButton: {
      type: String,
      default:"mb-4 text-body-2"
    },
    sizeButton: {
      type: String,
      default: "90%"
    }, 
    alignButton: {
      type: String,
      default:"center"
    },
    cardTextButton: {
      type: String,
      default: ""
    },    
    cardTextColorButton: {
      type: String,
      default: "white--text"
    }, 
    cardColorButton: {
      type: String,
      default: "secondary"
    },
    hrefButton: {
      type: String,
      default: "#"
    },
    cardTextButtonTwo: {
      type: String,
      default: ""
    },    
    cardTextColorButtonTwo: {
      type: String,
      default: "secondary--text"
    }, 
    cardColorButtonTwo: {
      type: String,
      default: "secondary"
    }, 
    hrefButtonTwo: {
      type: String,
      default: "#"
    },
    height: {
      type: Number,
      default: 250
    },
    boldTitle: {
      type: Boolean,
      default:false
    },
    justifyTitle: {
      type: String,
      default: "justify-center"
    },
    classTitle: {
      type: String,
      default:"text-body-1"
    },
    heightTitle:{
      type:String,
      default:"auto"
    },
    flat: {
      type: Boolean,
      default:false
    },
    tile: {
      type: Boolean,
      default:false
    }
  },
  data() {
    return {
    }
  },
  computed:{
    optionnalTitleClasses(){
      let classes = this.justifyTitle+' '+this.textColor+' '+this.classTitle;
      if(this.boldTitle) classes += ' font-weight-bold';

      return classes;
    }
  }
};
</script>
