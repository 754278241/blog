import '@/app';
require('normalize.css/normalize.css');
import './scss/home.scss';
import Header from './components/Header';
import Footer from './components/Footer';
import Banner from './components/Banner';
import ArticleList from './components/ArticleList';
import Pagination from './components/Pagination';
import router from './router';
const home = new Vue({
    el: '#app',
    router,
    data:{
      articles:[],
      page_sum:1
    },
    components:{
      'my-header':Header,
      'my-footer':Footer,
      'banner':Banner,
      'article-list':ArticleList,
      'pagination':Pagination
    },
    created(){
      this.$http.get('/getIndexData').then(response => {
        this.articles = response.body.articles;
        this.page_sum = response.body.page_sum;
      })
    }
});
