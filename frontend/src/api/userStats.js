import axios from '@axios'

class UserStats {

    statistics() {
        return axios.get('userStats/users/statistics')
    }
   
}

const userStats = new UserStats();

export default userStats;