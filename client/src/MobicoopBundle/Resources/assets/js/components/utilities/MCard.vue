<template>
  <v-card
    :class="textColor"
    :color="backgroundColor"
    :height="height"
  >
    <v-card-title
      class="body-1 justify-center"
      :class="textColor"
    >
      {{ cardTitle }}
    </v-card-title>
    <v-card-text
      :class="textColor"
      align="center"
    >
      {{ cardText }}
    </v-card-text>
    <v-card-actions>
      <v-row
        justify="center"
      >
        <v-btn
          rounded
          class="mb-4 body-2"
          :class="cardTextColorButton+' '+cardColorButton"
          :href="hrefButton"
          width="90%"
        >
          {{ cardTextButton }}
        </v-btn>
        <v-btn
          v-if="cardTextButtonTwo!==''"
          rounded
          class="ma-0 body-2"
          :class="cardTextColorButtonTwo+' '+cardColorButtonTwo"
          :href="hrefButtonTwo"
          width="90%"
        >
          {{ cardTextButtonTwo }}
        </v-btn>
      </v-row>
    </v-card-actions>
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
      default: 200
    }
  },
  data() {
    return {
    }
  },
};
</script>