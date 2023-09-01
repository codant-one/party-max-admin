import axios from '@axios'

class ProductosAliexpress {

    get(params) {
        return axios.get('productsAliexpress', {params})
    }
   
    create(data) {
        return axios.post('/productsAliexpress', data)
    }

    update(data, id) {
        return axios.put(`/productsAliexpress/${id}`, data)
    }

    delete(id){
        return axios.delete(`/productsAliexpress/${id}`)
    }
}

const productosAliexpress = new ProductosAliexpress();

export default productosAliexpress;