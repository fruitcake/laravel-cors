(function() {
  var CORS_SERVER;

  CORS_SERVER = 'localhost:9292';

  describe('CORS-AXIOS', function() {
    it('should allow access to dynamic resource', function(done) {

      const options = {
        method: 'GET',
        url: `http://${CORS_SERVER}/`
      };

      return axios(options).then((response) => {
        expect(response.data).to.eql('Hello world');
        return done();
      })
    });

    it('should allow post resource with headers', function(done) {
      const options = {
        method: 'POST',
        data: {'foo':'bar'},
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        url: `http://${CORS_SERVER}/`
      };

      return axios(options).then((response) => {
        expect(response.data).to.eql('Hello world');
        return done();
      })
    });

    return  it('should not allow post resource with credentials', function(done) {

      const options = {
        method: 'POST',
        headers: {
          'Authorization': 'Bearer 123',
        },
        withCredentials: true,
        url: `http://${CORS_SERVER}/auth`
      };

      return axios(options).then((response) => {
        // Should not come here
      })
        .catch(function(error) {
          console.log(error);
          return done();
        })
    });
  });

}).call(this);
