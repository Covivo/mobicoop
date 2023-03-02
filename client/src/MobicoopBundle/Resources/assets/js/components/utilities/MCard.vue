<template>
  <v-card
    :class="textColor+' '+cardRounded"
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
              <v-tooltip
                v-if="mustBeLoggedButtonTwo && !userId"
                bottom
              >
                <template v-slot:activator="{ on }">
                  <div
                    style="width:100%;"
                    v-on="on"
                  >
                    <v-btn
                      v-if="cardTextButtonTwo!==''"
                      :disabled="mustBeLoggedButtonTwo && !userId ? true : false"
                      text
                      rounded
                      class="ma-0 text-body-2"
                      :class="cardTextColorButtonTwo+' '+cardColorButtonTwo +' '+classButtonTwo"
                      :href="hrefButtonTwo"
                      width="95%"
                    >
                      {{ cardTextButtonTwo }}
                    </v-btn>
                  </div>
                </template>
                <span>{{ mustBeLoggedButtonTextTwo }}</span>
              </v-tooltip>
              <v-btn
                v-else-if="cardTextButtonTwo!==''"
                text
                rounded
                class="ma-0 text-body-2"
                :class="cardTextColorButtonTwo+' '+cardColorButtonTwo"
                :href="hrefButtonTwo"
                width="95%"
                v-bind="attrs"
                v-on="on"
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
    cardRounded: {
      type: String,
      default: ""
    },
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
    classButtonTwo: {
      type: String,
      default:"mb-4 text-body-2"
    },
    sizeButton: {
      type: String,
      default: "95%"
    },
    alignButton: {
      type: String,
      default:"center"
    },
    mustBeLoggedButton:{
      type: Boolean,
      default: false
    },
    mustBeLoggedButtonText:{
      type: String,
      default: null
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
    mustBeLoggedButtonTwo:{
      type: Boolean,
      default: false
    },
    mustBeLoggedButtonTextTwo:{
      type: String,
      default: null
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
    },
    image: {
      type: String,
      default:""
    },
    userId: {
      type: Number,
      default: null
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
