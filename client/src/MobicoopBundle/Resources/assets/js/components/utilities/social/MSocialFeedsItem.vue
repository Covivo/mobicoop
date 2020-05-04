<template>
  <div  
    @mouseenter="redirected()"
    v-html="iFrameString"
  />
</template>
<script>
export default {
  props:{
    iFrameString:{
      type: String,
      default: null
    }
  },
  data() {
    return {
      source: null
    }
  },
  mounted() {
    // get src in string
    let urlCurrent = JSON.parse(JSON.stringify(this.iFrameString.replace(/"/g,"'")));
    let regex = /<iframe.*?src='(.*?)'/;
    this.source = regex.exec(urlCurrent)[1];
  },
  methods:{
    redirected(){
      let source = this.source
      let monitor = setInterval(function(){
        let elem = document.activeElement;
        if(elem && elem.tagName == 'IFRAME'){
          clearInterval(monitor);
          // return window.location.href = "src";
          return window.open(source, "_blank");
        }
      }, 100);
    }
  }
}
</script>
