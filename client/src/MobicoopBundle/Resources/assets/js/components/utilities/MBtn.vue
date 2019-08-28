<template>
  <v-btn
    :color="color"
    rounded
  >
    <template v-slot:default>
      <span
        id="textButton"
        :class="classSpan"
      >
        <slot>  
          {{ textButton }}
        </slot>
      </span>
    </template>
  </v-btn>
</template>

<script>
// All values authorized for text format
let validatorTextFormat = ['capitalize', 'uppercase', 'lowercase', 'none', 'reverse', 'kebabcase', 'camelcase', 'pascalcase'];
// The text format that let the CSS do its magic without further treatment
let justCssStyle = ['capitalize', 'uppercase', 'lowercase', 'none'];

export default {
  props: {
    color: {
      type: String,
      default: "primary"
    },
    textFormat: {
      type: String,
      default: "none",
      validator: function (value) {
        // The value must match one of these strings
        return validatorTextFormat.indexOf(value) !== -1
      }
    },
    text:{
      type: String,
      default: "default"
    }
  },
  data() {
    return {
      textButton:this.text,
      classSpan:""
    }
  },
  mounted(){
    // Define the right class for the text format
    // On  capitalize, uppercase, lowercase and none we let the css do te job
    // On other valid cases we do the right treatment
    if(justCssStyle.indexOf(this.textFormat) !== -1){
      this.classSpan = this.textFormat;
    }
    else if(this.textFormat==="reverse"){
      this.classSpan = "none";
      document.getElementById("textButton").innerText = document.getElementById("textButton").innerText.split("").reverse().join("");
    }
    else if(this.textFormat==="kebabcase"){
      this.classSpan = "none";
      document.getElementById("textButton").innerText = document.getElementById("textButton").innerText.replace(new RegExp(" ", "g"),"-").toLowerCase();
    }
    else if(this.textFormat==="camelcase" || this.textFormat==="pascalcase"){
      let pascal = (this.textFormat==="pascalcase") ? pascal = true : pascal = false;
      document.getElementById("textButton").innerText = this.camelize(document.getElementById("textButton").innerText, pascal);
      this.classSpan = "none";
    }
  },
  methods:{
    camelize(str,pascal=false) {
      if (str == null || str == "") {
        return str;
      }
      let newText = "";
      let characters = str.split("");
      let nextUpper = pascal;
      for (let char of characters) {
        if(char!==" "){
          if(nextUpper){
            newText += char.toUpperCase();
            nextUpper = false;
          }
          else{
            newText += char.toLowerCase();
          }
          
        }
        else{
          nextUpper = true;
        }
      }
      
      return newText
    }
  }
}
</script>
<style lang="scss" scoped>
  #textButton{
    &.capitalize{
      text-transform: capitalize; 
    }
    &.uppercase{
      text-transform: uppercase; 
    }
    &.lowercase{
      text-transform: lowercase; 
    }
    &.none{
      text-transform: none; 
    }
  }
</style>